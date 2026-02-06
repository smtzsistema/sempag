<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Form;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RegistrationExportController extends Controller
{
    /**
     * Status oficiais do sistema (tbl_inscricao.ins_aprovado)
     * S = Aprovado | E = Em análise | R = Reprovado | N = Excluído
     */
    private const STATUSES = ['S', 'E', 'R', 'N'];

    public function index(Event $event)
    {
        return view('admin.inscricoes.exports.index', compact('event'));
    }

    public function modal(Event $event)
    {
        // carrega o que o modal precisa (categorias, etc) igual você já faz na index
        $categories = Category::where('eve_id', $event->id)->orderBy('cat_nome')->get();

        return view('admin.registrations.export_modal', compact('event', 'categories'));
    }

    public function filteredForm(Event $event)
    {
        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        $selectedCatIds = [];
        $selectedStatuses = ['S', 'E', 'R']; // padrão: não incluir excluídos

        return view('admin.inscricoes.exports.filtered', compact('event', 'categories', 'selectedCatIds', 'selectedStatuses'));
    }

    public function byCategoryForm(Event $event)
    {
        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        return view('admin.inscricoes.exports.by_category', compact('event', 'categories'));
    }

    public function byStatusForm(Event $event)
    {
        return view('admin.inscricoes.exports.by_status', compact('event'));
    }

    public function all(Event $event)
    {
        // Por padrão, não exporta excluídos
        $query = Registration::where('eve_id', $event->eve_id)
            ->where('ins_aprovado', '!=', 'N');

        // null => header por TODAS as categorias/fichas do evento
        return $this->downloadCsvFromForms($event, $query, 'inscricoes', null);
    }

    public function byCategory(Request $request, Event $event)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer'],
        ]);

        $categoryId = (int) $data['category_id'];

        // garante que a categoria pertence ao evento
        $allowed = Category::where('eve_id', $event->eve_id)
            ->where('cat_id', $categoryId)
            ->exists();
        abort_unless($allowed, 404);

        $query = Registration::where('eve_id', $event->eve_id)
            ->where('cat_id', $categoryId)
            ->where('ins_aprovado', '!=', 'N');

        return $this->downloadCsvFromForms($event, $query, 'inscricoes_categoria_' . $categoryId, [$categoryId]);
    }

    public function byStatus(Request $request, Event $event)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:S,E,R,N'],
        ]);

        $status = $data['status'];

        $query = Registration::where('eve_id', $event->eve_id)
            ->where('ins_aprovado', $status);

        // header por TODAS as categorias/fichas do evento (ou você pode decidir passar cat_ids via UI)
        return $this->downloadCsvFromForms($event, $query, 'inscricoes_status_' . $status, null);
    }

    /**
     * Export com filtros combinados (multi-seleção).
     * Se não selecionar nada em um filtro, assume "todos" naquele filtro.
     */
    public function filtered(Request $request, Event $event)
    {
        $data = $request->validate([
            'cat_ids' => ['nullable', 'array'],
            'cat_ids.*' => ['integer'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', 'in:S,E,R,N'],
        ]);

        $catIds = array_values(array_unique(array_map('intval', $data['cat_ids'] ?? [])));
        $statuses = array_values(array_unique($data['statuses'] ?? []));

        $query = Registration::where('eve_id', $event->eve_id);

        $query = $this->applyFilters($query, [
            'cat_ids' => $catIds,
            'statuses' => $statuses,
        ], $event);

        // se não selecionou categorias, usa null (todas)
        $headerCatIds = !empty($catIds) ? $catIds : null;

        return $this->downloadCsvFromForms($event, $query, 'inscricoes_filtradas', $headerCatIds);
    }

    private function applyFilters($query, array $filters, Event $event)
    {
        // Categorias (se vazio = todas)
        $catIds = $filters['cat_ids'] ?? [];
        if (!empty($catIds)) {
            $allowed = Category::where('eve_id', $event->eve_id)
                ->whereIn('cat_id', $catIds)
                ->pluck('cat_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            if (!empty($allowed)) {
                $query->whereIn('cat_id', $allowed);
            } else {
                $query->whereRaw('1=0');
            }
        }

        // Status (se vazio = todos)
        $statuses = $filters['statuses'] ?? [];
        if (!empty($statuses)) {
            $query->whereIn('ins_aprovado', $statuses);
        }

        return $query;
    }

    private function fmtDate($value, string $format = 'd/m/Y H:i'): string
    {
        if (empty($value)) return '';

        try {
            if ($value instanceof \DateTimeInterface) {
                return $value->format($format);
            }
            return Carbon::parse((string) $value)->format($format);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function statusLabel(?string $status): string
    {
        $status = (string) $status;

        return match ($status) {
            'S' => 'Aprovado',
            'E' => 'Em análise',
            'R' => 'Reprovado',
            'N' => 'Excluído',
            default => '',
        };
    }
    private function valueFromRegistrationByKey($r, ?string $key): string
    {
        $key = trim((string) $key);
        if ($key === '') return '';

        // 1) atributo direto
        if (isset($r->{$key}) && $r->{$key} !== null && $r->{$key} !== '') {
            return (string) $r->{$key};
        }

        // 2) fallback "ins_" + key (se key veio sem prefixo)
        $insKey = str_starts_with($key, 'ins_') ? $key : ('ins_' . $key);
        if (isset($r->{$insKey}) && $r->{$insKey} !== null && $r->{$insKey} !== '') {
            return (string) $r->{$insKey};
        }

        return '';
    }
    private function downloadCsvFromForms(Event $event, $query, string $baseFilename, ?array $catIdsForHeader)
    {
        // 1) Busca forms do evento (uma por categoria)
        $formsQuery = Form::where('eve_id', $event->eve_id)->with('fields');

        if (!empty($catIdsForHeader)) {
            $formsQuery->whereIn('cat_id', $catIdsForHeader);
        }

        $forms = $formsQuery->get();

        // Map de fields por categoria (pra preencher só o que existe naquela ficha)
        $formFieldsByCat = [];
        foreach ($forms as $form) {
            $catId = (int) ($form->cat_id ?? 0);
            if ($catId <= 0) continue;

            // Se tiver ordenação de field (ex: fic_ordem), você pode ordenar aqui
            $formFieldsByCat[$catId] = $form->fields ?? collect();
        }

        $fieldIdToCol = [];
        $headerCols = [];
        $usedCols = [];

        // Agrupa por key
        $groups = []; // key => [['field_id'=>, 'label'=>, 'cat_id'=>], ...]
        foreach ($forms as $form) {
            $catId = (int) ($form->cat_id ?? 0);
            foreach (($form->fields ?? []) as $field) {
                $key = (string) ($field->key ?? '');
                $label = trim((string) ($field->fic_label ?? $field->label ?? $field->name ?? $key));

                if ($label === '') {
                    $label = $key !== '' ? $key : ('field_' . $field->id);
                }

                if (!isset($groups[$key])) $groups[$key] = [];
                $groups[$key][] = [
                    'field_id' => (int) $field->id,
                    'label' => $label,
                    'cat_id' => $catId,
                ];
            }
        }

        // Helper pra evitar cabeçalho duplicado
        $uniqueCol = function (string $col) use (&$usedCols) {
            $base = $col;
            $i = 2;
            while (isset($usedCols[$col])) {
                $col = $base . '_' . $i;
                $i++;
            }
            $usedCols[$col] = true;
            return $col;
        };

        // Mantém ordem “natural”: percorre forms na ordem de retorno e fields na ordem do relacionamento
        foreach ($forms as $form) {
            foreach (($form->fields ?? []) as $field) {
                $key = (string) ($field->key ?? '');
                if (!array_key_exists($key, $groups)) continue;

                // Monta colunas desse grupo só uma vez, quando encontrar o primeiro field dele
                // (pra manter ordem estável)
                $alreadyBuilt = false;
                foreach ($groups[$key] as $g) {
                    if (isset($fieldIdToCol[$g['field_id']])) {
                        $alreadyBuilt = true;
                        break;
                    }
                }
                if ($alreadyBuilt) continue;

                $items = $groups[$key];
                $labels = array_values(array_unique(array_map(fn ($x) => $x['label'], $items)));

                if (count($labels) === 1) {
                    // labels iguais => uma coluna só
                    $col = $uniqueCol($labels[0]);
                    $headerCols[] = $col;

                    foreach ($items as $it) {
                        $fieldIdToCol[$it['field_id']] = $col;
                    }
                } else {
                    // labels diferentes => label1/label2/... (na ordem dos labels encontrados)
                    $idx = 1;
                    foreach ($labels as $lab) {
                        $col = $uniqueCol($lab . $idx);
                        $headerCols[] = $col;

                        foreach ($items as $it) {
                            if ($it['label'] === $lab) {
                                $fieldIdToCol[$it['field_id']] = $col;
                            }
                        }
                        $idx++;
                    }
                }

                // Remove o grupo pra não reconstruir
                unset($groups[$key]);
            }
        }

        // 3) Header final: colunas fixas + colunas da ficha
        $header = [
            'ID',
            'Categoria',
            'Status',
            'Data Cadastro',
        ];

        foreach ($headerCols as $col) {
            $header[] = $col;
        }

        $eventToken = $event->eve_token ?? $event->token ?? (string) ($event->eve_id ?? 'evento');
        $filename = $baseFilename . '_' . $eventToken . '_' . date('Y-m-d_H-i') . '.csv';

        $self = $this;

        return response()->streamDownload(function () use ($query, $header, $fieldIdToCol, $formFieldsByCat, $self) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 (Excel)
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $header, ';');

            $query
                ->with(['category', 'answers.field'])
                ->orderBy('ins_id')
                ->chunk(500, function ($regs) use ($out, $header, $fieldIdToCol, $formFieldsByCat, $self) {

                    foreach ($regs as $r) {
                        $row = array_fill_keys($header, '');

                        $catNome = (string) (
                            $r->category?->cat_nome
                            ?? $r->category?->name
                            ?? ''
                        );

                        $row['ID'] = (string) ($r->ins_id ?? $r->id ?? '');
                        $row['Categoria'] = $catNome;
                        $row['Status'] = $self->statusLabel($r->ins_aprovado ?? null);
                        $row['Data Cadastro'] = $self->fmtDate($r->created_at ?? null, 'd/m/Y H:i');

                        // Preenche primeiro pelas answers
                        foreach ($r->answers as $a) {
                            $col = $fieldIdToCol[$a->field_id] ?? null;
                            if (!$col) continue;

                            if (!is_null($a->value_text)) {
                                $row[$col] = (string) $a->value_text;
                            } elseif (!is_null($a->value_json)) {
                                $v = $a->value_json;
                                if (is_array($v)) {
                                    $row[$col] = implode(' | ', array_map(fn ($x) => (string) $x, $v));
                                } else {
                                    $row[$col] = (string) $v;
                                }
                            }
                        }

                        // Se não tiver answer, tenta preencher via key direto do registro
                        $catId = (int) ($r->cat_id ?? 0);
                        $fieldsThisCat = $formFieldsByCat[$catId] ?? null;

                        if ($fieldsThisCat) {
                            foreach ($fieldsThisCat as $field) {
                                $col = $fieldIdToCol[(int) $field->id] ?? null;
                                if (!$col) continue;

                                // se já veio de answers, não mexe
                                if (isset($row[$col]) && $row[$col] !== '') continue;

                                $key = (string) ($field->key ?? '');
                                $value = $self->valueFromRegistrationByKey($r, $key);

                                // se a key for data, tenta formatar (bem comum em field de cadastro/nascimento)
                                if ($value !== '' && str_contains(strtolower($key), 'data')) {
                                    $maybe = $self->fmtDate($value, 'd/m/Y');
                                    if ($maybe !== '') $value = $maybe;
                                }

                                if ($value !== '') {
                                    $row[$col] = $value;
                                }
                            }
                        }

                        // Mantém ordem do header
                        $ordered = [];
                        foreach ($header as $h) {
                            $ordered[] = $row[$h] ?? '';
                        }

                        fputcsv($out, $ordered, ';');
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
            'Pragma' => 'no-cache',
        ]);
    }
}
