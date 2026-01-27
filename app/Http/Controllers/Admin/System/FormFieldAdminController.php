<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FieldPreset;
use App\Provaiders\AppService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormFieldAdminController extends Controller
{
    public function index(Event $event, Form $form)
    {
        abort_unless((int)$form->eve_id === (int)$event->eve_id, 404);

        $form->load(['category', 'fields']);

        // keys já presentes na ficha
        $usedKeys = $form->fields
            ->pluck('ficg_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $usedGroups = $form->fields->pluck('ficg_id')->filter()->unique()->values()->all();
        // Presets (só os que NÃO estão na ficha)
        $presets = FieldPreset::query()
            ->when(!empty($usedGroups), fn($q) => $q->whereNotIn('ficg_id', $usedGroups))
            ->orderBy('ficg_group')
            ->orderBy('fic_label')
            ->get();

        return view('admin.system.form_fields.index', compact('event', 'form', 'presets'));
    }


    public function create(Event $event, Form $form)
    {
        // garante que o form pertence ao evento
        abort_unless((int)$form->eve_id === (int)$event->eve_id, 404);

        $field = new FormField([
            'fic_obrigatorio' => false,
            'fic_tipo' => 'text',
            'fic_edita' => 'N',
        ]);

        return view('admin.system.form_fields.create', compact('event', 'form', 'field'));
    }

    public function store(Request $request, Event $event, Form $form)
    {
        abort_unless((int)$form->eve_id === (int)$event->eve_id, 404);

        $data = $this->validateFieldCreate($request, $form);

        // ordem automática (mas o admin pode forçar uma ordem manual)
        $nextOrder = (int)(FormField::where('form_id', $form->form_id)->max('fic_ordem') ?? 0) + 10;
        $order = array_key_exists('order', $data) && $data['order'] !== null
            ? (int)$data['order']
            : $nextOrder;

        $field = FormField::create([
            'form_id' => $form->form_id,

            'fic_nome' => $data['key'],
            'fic_label' => $data['label'],
            'fic_tipo' => $data['type'],

            'fic_obrigatorio' => (bool)($data['required'] ?? false),
            'fic_edita' => (bool)($data['editable_by_attendee'] ?? false) ? 'S' : 'N',
            'fic_ordem' => $order,

            'fic_opcoes' => $this->normalizeOptions($data['options_text'] ?? null),
            'fic_validacoes' => $data['validation_rules'] ?? null,
            'fic_placeholder' => $data['placeholder'] ?? null,
            'fic_help_text' => $data['help_text'] ?? null,
        ]);

        return redirect()
            ->route('admin.system.forms.fields.edit', [$event, $form, $field])
            ->with('ok', 'Campo criado.');
    }

    public function edit(Event $event, Form $form, FormField $field)
    {
        // garante que o form pertence ao evento
        abort_unless((int)$form->eve_id === (int)$event->eve_id, 404);

        // garante que o field pertence ao form
        abort_unless((int)$field->form_id === (int)$form->form_id, 404);

        return view('admin.system.form_fields.edit', compact('event', 'form', 'field'));
    }

    public function update(Request $request, Event $event, Form $form, FormField $field)
    {
        abort_unless((int)$form->eve_id === (int)$event->eve_id, 404);
        abort_unless((int)$field->form_id === (int)$form->form_id, 404);

        $data = $this->validateFieldUpdate($request, $form);

        $field->update([
            // key não muda mais
            'fic_label' => $data['label'],
            'fic_tipo' => $data['type'],

            'fic_obrigatorio' => (bool)($data['required'] ?? false),
            'fic_edita' => (bool)($data['editable_by_attendee'] ?? false) ? 'S' : 'N',

            'fic_ordem' => array_key_exists('order', $data) && $data['order'] !== null
                ? (int)$data['order']
                : $field->fic_ordem,

            'fic_opcoes' => $this->normalizeOptions($data['options_text'] ?? null),
            'fic_validacoes' => $data['validation_rules'] ?? null,
            'fic_placeholder' => $data['placeholder'] ?? null,
            'fic_help_text' => $data['help_text'] ?? null,
        ]);

        return back()->with('ok', 'Campo atualizado.');
    }

    private function validateFieldCreate(Request $request, Form $form): array
    {
        return $request->validate([
            'key' => [
                'required',
                'string',
                'min:2',
                'max:80',
                Rule::unique('tbl_ficha', 'fic_nome')
                    ->where(fn($q) => $q->where('form_id', $form->form_id)),
            ],
            'label' => ['required', 'string', 'min:2', 'max:160'],
            'type' => ['required', 'string', 'in:text,textarea,email,number,select,multiselect,cpf,cnpj,cep,password'],
            'order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'required' => ['nullable'],
            'editable_by_attendee' => ['nullable', 'in:0,1'],
            'options_text' => ['nullable', 'string', 'max:10000'],
            'validation_rules' => ['nullable', 'string', 'max:5000'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function validateFieldUpdate(Request $request, Form $form): array
    {
        return $request->validate([
            'key' => ['prohibited'], // não deixa alterar no update
            'label' => ['required', 'string', 'min:2', 'max:160'],
            'type' => ['required', 'string', 'in:text,textarea,email,number,select,multiselect,cpf,cnpj,cep,password'],
            'order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'required' => ['nullable'],
            'editable_by_attendee' => ['nullable', 'in:0,1'],
            'options_text' => ['nullable', 'string', 'max:10000'],
            'validation_rules' => ['nullable', 'string', 'max:5000'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function normalizeOptions(?string $optionsText): ?array
    {
        $raw = trim((string)($optionsText ?? ''));
        if ($raw === '') return null;

        // 1) JSON array (builder)
        if (str_starts_with($raw, '[') || str_starts_with($raw, '{')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (array_is_list($decoded)) {
                    $out = array_values(array_filter(array_map(fn($v) => trim((string)$v), $decoded), fn($v) => $v !== ''));
                    return $out ?: null;
                }
                // objeto legado: pega valores
                $out = array_values(array_filter(array_map(fn($v) => trim((string)$v), array_values($decoded)), fn($v) => $v !== ''));
                return $out ?: null;
            }
        }

        // 2) fallback: 1 por linha
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $opts = [];
        foreach ($lines as $l) {
            $l = trim((string)$l);
            if ($l !== '') $opts[] = $l;
        }

        return $opts ?: null;
    }

    public function addPreset(Event $event, Form $form, FieldPreset $preset)
    {
        // garante que o form pertence ao evento
        abort_unless((int)$form->eve_id === (int)$event->eve_id, 404);

        // evita duplicar o mesmo fic_nome na ficha
        $exists = FormField::where('form_id', $form->form_id)
            ->where('fic_nome', $preset->fic_nome)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Esse campo já existe na ficha.');
        }

        $nextOrder = (int)(FormField::where('form_id', $form->form_id)->max('fic_ordem') ?? 0) + 10;

        // cria via relação pra garantir o form_id automaticamente
        $form->fields()->create([
            'fic_nome' => $preset->fic_nome,          // chave técnica
            'fic_label' => $preset->fic_label,        // label
            'fic_tipo' => $preset->fic_tipo,          // tipo
            'fic_opcoes' => $preset->fic_opcoes,      // json options
            'fic_placeholder' => $preset->fic_placeholder,
            'fic_help_text' => $preset->fic_help_text,
            'fic_obrigatorio' => (bool)$preset->fic_obrigatorio,
            'fic_ordem' => $nextOrder,

            // ✅ copia validações do preset
            'fic_validacoes' => $preset->fic_validacoes,

            // vínculo com o preset
            'ficg_id' => $preset->ficg_id,

            // default
            'fic_edita' => 'N',
        ]);

        return back()->with('success', 'Campo adicionado na ficha.');
    }

    public function reorder(Request $request, Event $event, Form $form)
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer'],
        ]);

        $order = array_values(array_unique(array_map('intval', $data['order'])));

        // PK real do model relacionado (no seu caso, fic_id)
        $pk = $form->fields()->getRelated()->getKeyName(); // "fic_id"

        // coluna de ordem (tenta sort_order, senão fic_ordem)
        $table = $form->fields()->getRelated()->getTable(); // "tbl_ficha"
        $orderCol = Schema::hasColumn($table, 'sort_order')
            ? 'sort_order'
            : (Schema::hasColumn($table, 'fic_ordem') ? 'fic_ordem' : 'sort_order');

        // garante que os IDs pertencem ao form
        $validIds = $form->fields()
            ->whereIn($pk, $order)
            ->pluck($pk)
            ->map(fn($v) => (int)$v)
            ->all();

        if (count($validIds) !== count($order)) {
            return response()->json(['ok' => false, 'msg' => 'IDs inválidos para esta ficha'], 422);
        }

        DB::transaction(function () use ($form, $order, $pk, $orderCol) {
            foreach ($order as $i => $id) {
                $form->fields()->where($pk, $id)->update([
                    $orderCol => $i + 1,
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function destroy(Event $event, Form $form, FormField $field)
    {
        // garante que o form pertence ao evento
        abort_unless((int)$form->eve_id === (int)$event->eve_id, 404);

        // garante que o field pertence a essa ficha
        abort_unless((int)$field->form_id === (int)$form->form_id, 404);

        $field->delete();

        return redirect()
            ->route('admin.system.forms.fields.index', [$event, $form])
            ->with('success', 'Campo removido da ficha.');
    }
}
