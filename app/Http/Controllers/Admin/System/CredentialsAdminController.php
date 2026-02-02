<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Credential;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CredentialsAdminController extends Controller
{
    public function index(Event $event)
    {
        $credentials = Credential::where('eve_id', $event->eve_id)
            ->orderByDesc('cre_id')
            ->paginate(20);

        $catMap = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get()
            ->keyBy('cat_id');

        $credentials->getCollection()->transform(function ($cred) use ($catMap) {
            $ids = $this->normalizeCategoryIds($cred->cat_id);
            $cred->category_names = collect($ids)
                ->map(fn($id) => $catMap[$id]->cat_nome ?? null)
                ->filter()
                ->values()
                ->all();
            return $cred;
        });

        return view('admin.system.credentials.index', compact('event', 'credentials'));
    }

    /**
     * Tela de escolha do tipo (Etiqueta/A4)
     */
    public function create(Event $event)
    {
        return view('admin.system.credentials.create', compact('event'));
    }

    /**
     * Builder A4 (novo)
     */
    public function createA4(Event $event)
    {
        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        // Campos fixos do tbl_inscricao (pra montar o dropdown)
        $baseFields = $this->baseRegistrationFields();

        // Campos da ficha do evento (todas as fichas)
        $formIds = Form::where('eve_id', $event->eve_id)->pluck('form_id');
        $formFields = FormField::whereIn('form_id', $formIds)
            ->orderBy('form_id')
            ->orderBy('fic_ordem')
            ->get();

        $credential = null;
        $selectedCategories = [];
        $initialConfig = $this->defaultA4Config();

        return view('admin.system.credentials.a4', compact(
            'event',
            'categories',
            'selectedCategories',
            'baseFields',
            'formFields',
            'credential',
            'initialConfig'
        ));
    }

    public function storeA4(Request $request, Event $event)
    {
        $data = $this->validateA4($request);

        $categoryIds = $this->normalizeCategoryIds($data['category_ids'] ?? []);

        $payload = [
            'eve_id' => $event->eve_id,
            'cre_nome' => $data['cre_nome'],
            'cre_tipo' => 'A4',
            'cat_id' => $categoryIds,
            'cre_espelhar' => !empty($data['cre_espelhar']) ? 'S' : 'N',
            'cre_config' => $data['cre_config'] ?? null,
        ];

        if ($request->hasFile('cre_fundo')) {
            $path = $this->storeBackground($request->file('cre_fundo'), $event);
            $payload['cre_fundo'] = $path;
        }

        Credential::create($payload);

        return redirect()
            ->route('admin.system.credentials.index', $event)
            ->with('ok', 'Credencial cadastrada.');
    }

    public function edit(Event $event, Credential $credential)
    {
        abort_unless((int) $credential->eve_id === (int) $event->eve_id, 404);

        if (($credential->cre_tipo ?? 'A4') !== 'A4') {
            return back()->with('err', 'Tipo de credencial ainda não suportado.');
        }

        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        $baseFields = $this->baseRegistrationFields();
        $formIds = Form::where('eve_id', $event->eve_id)->pluck('form_id');
        $formFields = FormField::whereIn('form_id', $formIds)
            ->orderBy('form_id')
            ->orderBy('fic_ordem')
            ->get();

        $selectedCategories = $this->normalizeCategoryIds($credential->cat_id);
        $initialConfig = $credential->cre_config ?: $this->defaultA4Config();

        return view('admin.system.credentials.a4', compact(
            'event',
            'categories',
            'selectedCategories',
            'baseFields',
            'formFields',
            'credential',
            'initialConfig'
        ));
    }

    public function updateA4(Request $request, Event $event, Credential $credential)
    {
        abort_unless((int) $credential->eve_id === (int) $event->eve_id, 404);

        $data = $this->validateA4($request);

        $categoryIds = $this->normalizeCategoryIds($data['category_ids'] ?? []);

        $payload = [
            'cre_nome' => $data['cre_nome'],
            'cat_id' => $categoryIds,
            'cre_espelhar' => !empty($data['cre_espelhar']) ? 'S' : 'N',
            'cre_config' => $data['cre_config'] ?? null,
        ];

        if ($request->hasFile('cre_fundo')) {
            if (!empty($credential->cre_fundo) && Storage::disk('public')->exists($credential->cre_fundo)) {
                Storage::disk('public')->delete($credential->cre_fundo);
            }

            $path = $this->storeBackground($request->file('cre_fundo'), $event);
            $payload['cre_fundo'] = $path;
        }

        $credential->update($payload);

        return back()->with('ok', 'Credencial atualizada.');
    }

    public function destroy(Event $event, Credential $credential)
    {
        abort_unless((int) $credential->eve_id === (int) $event->eve_id, 404);

        if (!empty($credential->cre_fundo) && Storage::disk('public')->exists($credential->cre_fundo)) {
            Storage::disk('public')->delete($credential->cre_fundo);
        }

        $credential->delete();

        return redirect()
            ->route('admin.system.credentials.index', $event)
            ->with('ok', 'Credencial excluída.');
    }

    // -------------------------
    // Helpers
    // -------------------------

    private function validateA4(Request $request): array
    {
        $request->merge([
            'cre_espelhar' => $request->boolean('cre_espelhar'),
        ]);

        $validated = $request->validate([
            'cre_nome' => ['required', 'string', 'min:2', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:tbl_categorias,cat_id'],

            'cre_fundo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],

            // config JSON (vem como string no hidden)
            'cre_config' => ['required', 'string', 'max:200000'],
            'cre_espelhar' => ['nullable', 'boolean'],
        ], [
            'category_ids.required' => 'Selecione pelo menos 1 categoria.',
        ]);

        $cfg = json_decode($validated['cre_config'] ?? '', true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($cfg)) {
            throw ValidationException::withMessages([
                'cre_config' => 'Configuração inválida (JSON).',
            ]);
        }

        $validated['cre_config'] = $cfg;

        return $validated;
    }

    private function storeBackground($file, Event $event): string
    {
        $token = $event->eve_token ?? $event->eve_slug ?? (string) $event->eve_id;
        $folder = $token . '/credenciais';
        $filename = $file->hashName();
        return $file->storeAs($folder, $filename, 'public');
    }

    private function baseRegistrationFields(): array
    {
        return [
            ['key' => 'ins_id', 'label' => 'Inscrição # (ins_id)'],
            ['key' => 'ins_token', 'label' => 'Token (ins_token)'],

            ['key' => 'ins_nome', 'label' => 'Nome (ins_nome)'],
            ['key' => 'ins_sobrenome', 'label' => 'Sobrenome (ins_sobrenome)'],
            ['key' => 'ins_nomecracha', 'label' => 'Nome para crachá (ins_nomecracha)'],

            ['key' => 'ins_foto', 'label' => 'Foto (ins_foto)'],

            ['key' => 'ins_email', 'label' => 'E-mail (ins_email)'],
            ['key' => 'ins_cpf', 'label' => 'CPF (ins_cpf)'],
            ['key' => 'ins_cnpj', 'label' => 'CNPJ (ins_cnpj)'],
            ['key' => 'ins_tel_celular', 'label' => 'Celular (ins_tel_celular)'],
            ['key' => 'ins_instituicao', 'label' => 'Empresa/Instituição (ins_instituicao)'],
            ['key' => 'ins_siglainstituicao', 'label' => 'Empresa (credencial) (ins_siglainstituicao)'],
            ['key' => 'ins_cargo', 'label' => 'Cargo (ins_cargo)'],
            ['key' => 'ins_cargo_cred', 'label' => 'Cargo (credencial) (ins_cargo_cred)'],
        ];
    }

    private function defaultA4Config(): array
    {
        return [
            'page' => [
                'w' => 794,
                'h' => 1123,
            ],
            'elements' => [],
        ];
    }

    private function normalizeCategoryIds($value): array
    {
        if (is_null($value) || $value === '') return [];

        if (is_array($value)) {
            return array_values(array_unique(array_map('intval', $value)));
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_values(array_unique(array_map('intval', $decoded)));
            }
        }

        if (is_numeric($value)) {
            return [(int) $value];
        }

        return [];
    }
}
