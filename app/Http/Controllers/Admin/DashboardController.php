<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Credential;
use App\Models\Event;
use App\Models\Form;
use App\Models\Letter;
use App\Models\Registration;

class DashboardController extends Controller
{
    public function index(Event $event)
    {
        $u = auth()->user();

        // Se não puder ver dashboard, manda pra um lugar que ele consiga (opcional, mas faz sentido)
        if ($u && !$u->can('dashboard.view')) {
            if ($u->can('registrations.view')) {
                return redirect()->route('admin.registrations.index', $event);
            }
            // fallback: se não tiver outra permissão, deixa cair no dashboard mesmo (mas com dados)
        }

        // Base: não contar "excluídos" (N)
        $base = Registration::query()
            ->where('eve_id', $event->id)
            ->where('ins_aprovado', '!=', 'N');

        // Status oficiais: S/E/R/N
        $counts = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('ins_aprovado', 'E')->count(),
            'approved' => (clone $base)->where('ins_aprovado', 'S')->count(),
            'rejected' => (clone $base)->where('ins_aprovado', 'R')->count(),
        ];

        $countsByCategory = Registration::query()
            ->where('eve_id', $event->id)
            ->where('ins_aprovado', '!=', 'N')
            ->select('cat_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN ins_aprovado = 'E' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN ins_aprovado = 'S' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN ins_aprovado = 'R' THEN 1 ELSE 0 END) as rejected")
            ->with('category')
            ->groupBy('cat_id')
            ->orderByDesc('total')
            ->get();

        $latest = Registration::query()
            ->where('eve_id', $event->id)
            ->where('ins_aprovado', '!=', 'N')
            ->with('category')
            ->orderByDesc('ins_id')
            ->limit(10)
            ->get();

        /**
         * -------------------------
         * Mural de avisos (por categoria)
         * -------------------------
         * Regras:
         * - Ficha: precisa existir form ativo
         * - Cartas:
         *    - sem aprova/reprova (cat_aprova=false): precisa de S
         *    - com aprova/reprova (cat_aprova=true): precisa de E,S,R
         * - Credencial: precisa ter credencial vinculada à categoria (e com config preenchida)
         */
        $categories = Category::query()
            ->where('eve_id', $event->id)
            ->orderBy('cat_nome')
            ->get();

        // Carrega de uma vez (pra não virar N+1 no talo)
        $formsActive = Form::query()
            ->where('eve_id', $event->id)
            ->where('form_ativo', true)
            ->select(['form_id', 'cat_id'])
            ->get()
            ->groupBy('cat_id');

        $lettersByCat = Letter::query()
            ->where('eve_id', $event->id)
            ->select(['car_id', 'car_tipo', 'cat_id'])
            ->get()
            ->flatMap(function ($l) {
                $cats = $l->cat_id;
                if (is_string($cats)) {
                    $decoded = json_decode($cats, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $cats = $decoded;
                }
                if (!is_array($cats)) $cats = [];

                return collect($cats)->map(function ($catId) use ($l) {
                    return [
                        'cat_id' => (int)$catId,
                        'car_tipo' => (string)($l->car_tipo ?? ''),
                    ];
                });
            })
            ->groupBy('cat_id');

        $credsByCat = Credential::query()
            ->where('eve_id', $event->id)
            ->select(['cre_id', 'cat_id', 'cre_config'])
            ->get()
            ->flatMap(function ($c) {
                $cats = $c->cat_id;
                if (is_string($cats)) {
                    $decoded = json_decode($cats, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $cats = $decoded;
                }
                if (!is_array($cats)) $cats = [];

                $hasConfig = !empty($c->cre_config) && (is_array($c->cre_config) ? true : true);

                return collect($cats)->map(function ($catId) use ($c, $hasConfig) {
                    return [
                        'cat_id' => (int)$catId,
                        'cre_id' => (int)$c->cre_id,
                        'has_config' => (bool)$hasConfig,
                    ];
                });
            })
            ->groupBy('cat_id');

        $noticeBoard = $categories->map(function (Category $cat) use ($event, $formsActive, $lettersByCat, $credsByCat) {
            $catId = (int)$cat->cat_id;

            // 1) Form
            $hasFormActive = isset($formsActive[$catId]) && $formsActive[$catId]->isNotEmpty();

            // 2) Letters (conforme cat_aprova)
            $requiredLetters = $cat->requiresApproval ? ['E', 'S', 'R'] : ['S'];

            $presentLetters = collect($lettersByCat[$catId] ?? [])
                ->pluck('car_tipo')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $missingLetters = array_values(array_diff($requiredLetters, $presentLetters));

            $lettersOk = empty($missingLetters);

            // 3) Credential
            $hasCred = collect($credsByCat[$catId] ?? [])
                ->contains(fn($row) => !empty($row['cre_id']) && !empty($row['has_config']));

            // Mensagens/Issues
            $issues = [];
// URLs (filtrando pela categoria)
            $formsUrl = route('admin.system.forms.index', $event) . '?cat_id=' . $catId;
            $lettersUrl = route('admin.system.letters.index', $event) . '?cat_id=' . $catId;
            $credsUrl = route('admin.system.credentials.index', $event) . '?cat_id=' . $catId;

            if (!$hasFormActive) {
                $issues[] = [
                    'type' => 'form',
                    'text' => 'Formulário não configurado (nenhuma ficha ativa).',
                    'url' => $formsUrl,
                    'action' => 'Configurar ficha',
                ];
            }

            if (!$lettersOk) {
                $labels = [
                    'E' => 'Em análise',
                    'S' => 'Aprovado',
                    'R' => 'Reprovado',
                ];

                if ($cat->requiresApproval) {
                    if (count($presentLetters) === 0) {
                        $issues[] = [
                            'type' => 'letters',
                            'text' => 'Faltam as 3 cartas: Em análise, Aprovado, Reprovado.',
                            'url' => $lettersUrl,
                            'action' => 'Configurar cartas',
                        ];
                    } else {
                        $faltam = collect($missingLetters)->map(fn($s) => $labels[$s] ?? $s)->implode(', ');
                        $issues[] = [
                            'type' => 'letters',
                            'text' => 'Faltam cartas: ' . $faltam . '.',
                            'url' => $lettersUrl,
                            'action' => 'Configurar cartas',
                        ];
                    }
                } else {
                    $issues[] = [
                        'type' => 'letters',
                        'text' => 'Falta carta de confirmação: Aprovado.',
                        'url' => $lettersUrl,
                        'action' => 'Configurar carta',
                    ];
                }
            }

            if (!$hasCred) {
                $issues[] = [
                    'type' => 'credential',
                    'text' => 'Credencial não configurada pra essa categoria.',
                    'url' => $credsUrl,
                    'action' => 'Configurar credencial',
                ];
            }

            $ok = $hasFormActive && $lettersOk && $hasCred;

            return [
                'category' => $cat,
                'ok' => $ok,
                'has_form' => $hasFormActive,
                'letters_required' => $requiredLetters,
                'letters_present' => $presentLetters,
                'letters_missing' => $missingLetters,
                'has_credential' => $hasCred,
                'issues' => $issues,
            ];
        });

        return view('admin.dashboard', compact(
            'event',
            'counts',
            'countsByCategory',
            'latest',
            'noticeBoard'
        ));
    }
}
