<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Credential;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Support\RegistrationPhoto;

class AttendeeCredentialController extends Controller
{
    private function currentRegistration(Event $event): Registration
    {
        $insId = (int)session('attendee.ins_id');

        return Registration::where('ins_id', $insId)
            ->where('eve_id', $event->eve_id)
            ->firstOrFail();
    }

    private function credentialsForRegistration(Event $event, Registration $registration)
    {
        $catId = (int)($registration->cat_id ?? 0);

        return Credential::query()
            ->where('eve_id', $event->eve_id)
            ->where('cre_tipo', 'A4')
            ->whereJsonContains('cat_id', $catId)
            ->orderByDesc('cre_id')
            ->get();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'S' => 'Aprovada',
            'E' => 'Em análise',
            'R' => 'Reprovada',
            'N' => 'Excluída',
            default => $status,
        };
    }

    /**
     * Rota "inteligente":
     * - se não aprovado => volta com erro
     * - se 1 credencial => redireciona direto pra imprimir
     * - se >1 => vai pra escolha
     */
    public function entry(Event $event)
    {
        $registration = $this->currentRegistration($event);
        $status = (string)($registration->ins_aprovado ?? '');

        if ($status !== 'S') {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Credencial indisponível. Status da inscrição: ' . $this->statusLabel($status) . '.');
        }

        $creds = $this->credentialsForRegistration($event, $registration);

        if ($creds->count() === 1) {
            return redirect()->route('public.attendee.credentials.print', [$event, $creds->first()->cre_id]);
        }

        if ($creds->count() > 1) {
            return redirect()->route('public.attendee.credentials.choose', $event);
        }

        return redirect()
            ->route('public.attendee.area', $event)
            ->with('error', 'Nenhuma credencial disponível pra sua categoria.');
    }

    /**
     * Tela de escolha (quando tem mais de 1 credencial)
     */
    public function choose(Event $event)
    {
        $registration = $this->currentRegistration($event);
        $status = (string)($registration->ins_aprovado ?? '');

        if ($status !== 'S') {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Credencial indisponível. Status da inscrição: ' . $this->statusLabel($status) . '.');
        }

        $credentials = $this->credentialsForRegistration($event, $registration);

        if ($credentials->count() === 1) {
            return redirect()->route('public.attendee.credentials.print', [$event, $credentials->first()->cre_id]);
        }

        if ($credentials->isEmpty()) {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Nenhuma credencial disponível pra sua categoria.');
        }

        return view('public.attendee.credentials.choose', compact('event', 'registration', 'credentials'));
    }

    /**
     * Tela de impressão (A4)
     */
    public function print(Event $event, Credential $credential)
    {
        $registration = $this->currentRegistration($event);
        $status = (string)($registration->ins_aprovado ?? '');

        if ($status !== 'S') {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Credencial indisponível. Status da inscrição: ' . $this->statusLabel($status) . '.');
        }

        // segurança: credencial tem que ser do evento e bater categoria
        if ((int)$credential->eve_id !== (int)$event->eve_id) abort(404);

        $catId = (int)($registration->cat_id ?? 0);
        $cats = is_array($credential->cat_id) ? $credential->cat_id : (is_string($credential->cat_id) ? json_decode($credential->cat_id, true) : []);
        $cats = is_array($cats) ? array_map('intval', $cats) : [];

        if (!in_array($catId, $cats, true)) abort(404);

        $cfg = is_array($credential->cre_config) ? $credential->cre_config : (is_string($credential->cre_config) ? json_decode($credential->cre_config, true) : []);
        if (!is_array($cfg)) $cfg = [];

        $pageW = (int)(($cfg['page']['w'] ?? 794));
        $pageH = (int)(($cfg['page']['h'] ?? 1123));
        $elements = is_array($cfg['elements'] ?? null) ? $cfg['elements'] : [];

        $answers = RegistrationAnswer::query()
            ->where('eve_id', $event->eve_id)
            ->where('ins_id', $registration->ins_id)
            ->get()
            ->keyBy('fic_id');

        $resolved = array_map(function ($el) use ($registration, $answers) {
            if (!is_array($el)) return $el;

            $src = (string)($el['source'] ?? '');
            $raw = $this->resolveSource($src, $registration, $answers);

            // ✅ foto não aplica regras de texto (upper/limit/etc), senão quebra URL
            if (($el['type'] ?? '') === 'photo') {
                $el['value'] = trim((string)$raw);
                return $el;
            }

            [$val, $effectiveFont] = $this->applyCredentialRules($raw, $el);

            $el['value'] = $val;
            if ($effectiveFont !== null) {
                $el['effectiveFontSize'] = $effectiveFont;
            }

            return $el;
        }, $elements);



        $bgUrl = null;
        if (!empty($credential->cre_fundo)) {
            $bgUrl = asset('storage/' . $credential->cre_fundo);
        }

        $mirror = ((string)($credential->cre_espelhar ?? 'N')) === 'S';

        return view('public.attendee.credentials.print', compact(
            'event',
            'registration',
            'credential',
            'pageW',
            'pageH',
            'resolved',
            'bgUrl',
            'mirror'
        ));
    }

    private function applyCredentialRules(string $value, array $el): array
    {
        $v = trim($value);

        $formatMode = (string)($el['formatMode'] ?? 'none');
        $validationMode = (string)($el['validationMode'] ?? 'none');

        $limit = (int)($el['limit'] ?? 0);
        $fontWhenOver = (int)($el['fontWhenOver'] ?? 0);

        $effectiveFont = null;

        // --- validações ---
        if ($limit > 0 && $v !== '') {
            $len = mb_strlen($v, 'UTF-8');

            if ($validationMode === 'first_last' && $len > $limit) {
                $words = preg_split('/\s+/u', $v, -1, PREG_SPLIT_NO_EMPTY);
                if ($words && count($words) >= 2) {
                    $v = $words[0] . ' ' . $words[count($words) - 1];
                } elseif ($words && count($words) === 1) {
                    $v = $words[0];
                }
            }

            if ($validationMode === 'limit' && $len > $limit) {
                $v = mb_substr($v, 0, $limit, 'UTF-8');
            }

            if ($validationMode === 'limit_font') {
                if ($len > $limit && $fontWhenOver > 0) {
                    $effectiveFont = $fontWhenOver;
                }
            }
        }

        // --- formatação ---
        if ($v !== '') {
            if ($formatMode === 'upper') {
                $v = mb_strtoupper($v, 'UTF-8');
            } elseif ($formatMode === 'title') {
                $v = mb_convert_case($v, MB_CASE_TITLE, 'UTF-8');
            }
        }

        return [$v, $effectiveFont];
    }


    private function resolveSource(string $source, Registration $reg, $answers): string
    {
        $source = trim($source);
        if ($source === '') return '';

        if (str_starts_with($source, 'reg:')) {
            $key = substr($source, 4);

            if ($key === 'ins_id') return (string)($reg->ins_id ?? '');
            if ($key === 'ins_foto') return (string)(RegistrationPhoto::activeUrl($reg) ?? '');
            if ($key === 'ins_token') return (string)($reg->ins_token ?? '');

            $v = $reg->getAttribute($key);
            if (is_null($v)) return '';
            if (is_array($v)) return implode('; ', array_map('strval', $v));
            return (string)$v;
        }

        if (str_starts_with($source, 'ans:')) {
            $id = (int)substr($source, 4);
            if ($id <= 0) return '';

            $a = $answers->get($id);
            if (!$a) return '';

            $txt = (string)($a->res_valor_texto ?? '');
            if (trim($txt) !== '') return $txt;

            $j = $a->res_valor_json;
            if (is_array($j)) {
                // lista => "A; B; C"
                $isList = array_keys($j) === range(0, count($j) - 1);
                if ($isList) {
                    $vals = array_values(array_filter(array_map(fn($x) => trim((string)$x), $j), fn($x) => $x !== ''));
                    return implode('; ', $vals);
                }
                // objeto => json
                return json_encode($j, JSON_UNESCAPED_UNICODE);
            }

            return '';
        }

        return '';
    }
}
