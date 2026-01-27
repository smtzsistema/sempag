<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Form;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $q = trim((string) $request->query('q', ''));

        $rooms = DB::table('tbl_presenca')
            ->where('eve_id', $event->eve_id)
            ->when($q !== '', fn ($qq) => $qq->where('pre_local', 'like', "%{$q}%"))
            ->groupBy('pre_local')
            ->orderBy('pre_local')
            ->select([
                'pre_local',
                DB::raw('COUNT(*) as total_rows'),
                DB::raw('COUNT(DISTINCT ins_id) as unique_ins'),
                DB::raw('MIN(pre_data) as first_at'),
                DB::raw('MAX(pre_data) as last_at'),
            ])
            ->get();

        return view('admin.attendance.index', compact('event', 'rooms', 'q'));
    }

    public function export(Request $request, Event $event): StreamedResponse
    {
        $local = trim((string) $request->query('local', ''));
        $mode  = (string) $request->query('mode', 'all'); // all | unique

        abort_if($local === '', 404);
        if (!in_array($mode, ['all', 'unique'], true)) $mode = 'all';

        // ---------------------------------------------------------
        // 1) Descobre quais categorias aparecem nessa sala
        // (pra montar header com união das fichas envolvidas)
        // ---------------------------------------------------------
        $catIds = DB::table('tbl_presenca as p')
            ->leftJoin('tbl_inscricao as i', 'i.ins_id', '=', 'p.ins_id')
            ->where('p.eve_id', $event->eve_id)
            ->where('p.pre_local', $local)
            ->whereNotNull('i.cat_id')
            ->distinct()
            ->pluck('i.cat_id')
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        // Se não achou categoria (ex: tabela externa veio sem casar com inscrição),
        // ainda exporta só os campos de presença.
        $forms = collect();
        if (!empty($catIds)) {
            $forms = Form::where('eve_id', $event->eve_id)
                ->whereIn('cat_id', $catIds)
                ->with('fields')
                ->get();
        }

        // Map de fields por categoria (pra fallback por key)
        $formFieldsByCat = [];
        foreach ($forms as $form) {
            $cid = (int) ($form->cat_id ?? 0);
            if ($cid <= 0) continue;
            $formFieldsByCat[$cid] = $form->fields ?? collect();
        }

        // ---------------------------------------------------------
        // 2) Monta colunas da ficha (igual export de inscrições)
        // - agrupa por key
        // - se labels diferentes: Label1/Label2...
        // - se labels iguais: uma coluna só
        // ---------------------------------------------------------
        $fieldIdToCol = [];
        $headerCols = [];
        $usedCols = [];

        $groups = []; // key => [['field_id'=>, 'label'=>], ...]
        foreach ($forms as $form) {
            foreach (($form->fields ?? []) as $field) {
                $key = (string) ($field->key ?? '');
                $label = trim((string) ($field->fic_label ?? $field->label ?? $key));
                if ($label === '') $label = $key !== '' ? $key : ('field_' . $field->id);

                if (!isset($groups[$key])) $groups[$key] = [];
                $groups[$key][] = [
                    'field_id' => (int) $field->id,
                    'label' => $label,
                ];
            }
        }

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

        foreach ($forms as $form) {
            foreach (($form->fields ?? []) as $field) {
                $key = (string) ($field->key ?? '');
                if (!array_key_exists($key, $groups)) continue;

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
                    $col = $uniqueCol($labels[0]);
                    $headerCols[] = $col;
                    foreach ($items as $it) $fieldIdToCol[$it['field_id']] = $col;
                } else {
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

                unset($groups[$key]);
            }
        }

        // ---------------------------------------------------------
        // 3) Header final = labels da presença + labels da ficha
        // ---------------------------------------------------------
        $presenceHeader = [
            'Data/Hora',
            'Sala',
            'Tipo',
            'Via',
            'Inscrição',
            'Categoria',
        ];


        $header = array_merge($presenceHeader, $headerCols);

        // ---------------------------------------------------------
        // 4) Query das presenças (com/sem duplicidade)
        // ---------------------------------------------------------
        $presenceQuery = DB::table('tbl_presenca as p')
            ->where('p.eve_id', $event->eve_id)
            ->where('p.pre_local', $local)
            ->leftJoin('tbl_inscricao as i', 'i.ins_id', '=', 'p.ins_id')
            ->leftJoin('tbl_categorias as c', 'c.cat_id', '=', 'i.cat_id')
            ->select([
                'p.pre_id',
                'p.pre_data',
                'p.pre_local',
                'p.pre_tipo',
                'p.pre_via',
                'p.ins_id',
                'i.cat_id',
                'c.cat_nome',
            ]);

        if ($mode === 'unique') {
            $minDate = DB::table('tbl_presenca')
                ->select('ins_id', DB::raw('MIN(pre_data) as min_data'))
                ->where('eve_id', $event->eve_id)
                ->where('pre_local', $local)
                ->groupBy('ins_id');

            $minIds = DB::table('tbl_presenca as p2')
                ->joinSub($minDate, 'd', function ($join) {
                    $join->on('p2.ins_id', '=', 'd.ins_id')
                        ->on('p2.pre_data', '=', 'd.min_data');
                })
                ->where('p2.eve_id', $event->eve_id)
                ->where('p2.pre_local', $local)
                ->groupBy('p2.ins_id')
                ->select(DB::raw('MIN(p2.pre_id) as pre_id'));

            $presenceQuery->whereIn('p.pre_id', $minIds);
        }

        $presenceQuery->orderBy('p.pre_data')->orderBy('p.pre_id');

        $safeLocal = preg_replace('/[^a-zA-Z0-9\-_]+/u', '_', $local);
        $eventToken = $event->eve_token ?? (string) $event->eve_id;
        $filename = "presenca_{$eventToken}_{$safeLocal}_{$mode}_" . date('Y-m-d_H-i') . ".csv";

        $self = $this;

        return response()->streamDownload(function () use (
            $presenceQuery,
            $header,
            $fieldIdToCol,
            $formFieldsByCat,
            $self
        ) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 (Excel)
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');

            $presenceQuery->chunk(500, function ($presRows) use (
                $out,
                $header,
                $fieldIdToCol,
                $formFieldsByCat,
                $self
            ) {
                $insIds = collect($presRows)
                    ->pluck('ins_id')
                    ->filter(fn ($v) => $v !== null && $v !== '')
                    ->unique()
                    ->values()
                    ->all();

                $regsById = collect();
                if (!empty($insIds)) {
                    $regsById = Registration::whereIn('ins_id', $insIds)
                        ->with(['answers.field'])
                        ->get()
                        ->keyBy('ins_id');
                }

                foreach ($presRows as $p) {
                    $row = array_fill_keys($header, '');

                    // Presença (labels bonitinhas)
                    $row['Data/Hora'] = $self->fmtDate($p->pre_data ?? null, 'd/m/Y H:i:s');
                    $row['Sala']      = (string) ($p->pre_local ?? '');
                    $row['Tipo']      = (string) ($p->pre_tipo ?? '');
                    $row['Via']       = (string) ($p->pre_via ?? '');
                    $row['Inscrição'] = (string) ($p->ins_id ?? '');
                    $row['Categoria'] = (string) ($p->cat_nome ?? '');

                    // Campos da ficha (se existir inscrição vinculada)
                    $r = $regsById[$p->ins_id] ?? null;
                    if ($r) {
                        // 1) answers
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

                        // 2) fallback via key (somente campos dessa categoria)
                        $catId = (int) ($r->cat_id ?? 0);
                        $fieldsThisCat = $formFieldsByCat[$catId] ?? null;

                        if ($fieldsThisCat) {
                            foreach ($fieldsThisCat as $field) {
                                $col = $fieldIdToCol[(int) $field->id] ?? null;
                                if (!$col) continue;

                                if (isset($row[$col]) && $row[$col] !== '') continue;

                                $key = (string) ($field->key ?? '');
                                $value = $self->valueFromRegistrationByKey($r, $key);

                                if ($value !== '' && str_contains(strtolower($key), 'data')) {
                                    $maybe = $self->fmtDate($value, 'd/m/Y');
                                    if ($maybe !== '') $value = $maybe;
                                }

                                if ($value !== '') $row[$col] = $value;
                            }
                        }
                    }

                    // escreve na ordem do header
                    $ordered = [];
                    foreach ($header as $h) $ordered[] = $row[$h] ?? '';
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

    /**
     * 1) tenta $r->{$key}
     * 2) tenta $r->{"ins_{$key}"} (fallback)
     */
    private function valueFromRegistrationByKey($r, ?string $key): string
    {
        $key = trim((string) $key);
        if ($key === '') return '';

        if (isset($r->{$key}) && $r->{$key} !== null && $r->{$key} !== '') {
            return (string) $r->{$key};
        }

        $insKey = str_starts_with($key, 'ins_') ? $key : ('ins_' . $key);
        if (isset($r->{$insKey}) && $r->{$insKey} !== null && $r->{$insKey} !== '') {
            return (string) $r->{$insKey};
        }

        return '';
    }
}
