<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CertificatesAdminController extends Controller
{
    public function index(Event $event)
    {
        $certificates = Certificate::where('eve_id', $event->eve_id)
            ->orderByDesc('cer_id')
            ->paginate(20);

        $catMap = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get()
            ->keyBy('cat_id');

        $certificates->getCollection()->transform(function ($cert) use ($catMap) {
            $ids = $this->normalizeCategoryIds($cert->cat_id);
            $cert->category_names = collect($ids)
                ->map(fn($id) => $catMap[$id]->cat_nome ?? null)
                ->filter()
                ->values()
                ->all();
            return $cert;
        });

        return view('admin.system.certificates.index', compact('event', 'certificates'));
    }

    public function create(Event $event)
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

        $certificate = null;
        $selectedCategories = [];
        $initialConfig = $this->defaultA4ConfigDeitado();

        return view('admin.system.certificates.a4', compact(
            'event',
            'categories',
            'selectedCategories',
            'baseFields',
            'formFields',
            'certificate',
            'initialConfig'
        ));
    }

    public function storeA4(Request $request, Event $event)
    {
        $data = $this->validateA4($request);

        $categoryIds = $this->normalizeCategoryIds($data['category_ids'] ?? []);

        $payload = [
            'eve_id' => $event->eve_id,
            'cer_nome' => $data['cer_nome'],
            'cer_tipo' => 'A4',
            'cat_id' => $categoryIds,
            'cer_espelhar' => !empty($data['cer_espelhar']) ? 'S' : 'N',
            'cer_config' => $data['cer_config'] ?? null,
        ];

        if ($request->hasFile('cer_fundo')) {
            $path = $this->storeBackground($request->file('cer_fundo'), $event);
            $payload['cer_fundo'] = $path;
        }

        Certificate::create($payload);

        return redirect()
            ->route('admin.system.certificates.index', $event)
            ->with('ok', 'Certificado cadastrado.');
    }

    public function edit(Event $event, Certificate $certificate)
    {
        abort_unless((int)$certificate->eve_id === (int)$event->eve_id, 404);

        if (($certificate->cer_tipo ?? 'A4') !== 'A4') {
            return back()->with('err', 'Tipo de certificado ainda não suportado.');
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

        $selectedCategories = $this->normalizeCategoryIds($certificate->cat_id);
        $initialConfig = $certificate->cer_config ?: $this->defaultA4Config();

        return view('admin.system.certificates.a4', compact(
            'event',
            'categories',
            'selectedCategories',
            'baseFields',
            'formFields',
            'certificate',
            'initialConfig'
        ));
    }

    public function updateA4(Request $request, Event $event, Certificate $certificate)
    {
        abort_unless((int)$certificate->eve_id === (int)$event->eve_id, 404);

        $data = $this->validateA4($request);

        $categoryIds = $this->normalizeCategoryIds($data['category_ids'] ?? []);

        $payload = [
            'cer_nome' => $data['cer_nome'],
            'cat_id' => $categoryIds,
            'cer_espelhar' => !empty($data['cer_espelhar']) ? 'S' : 'N',
            'cer_config' => $data['cer_config'] ?? null,
        ];

        if ($request->hasFile('cer_fundo')) {
            if (!empty($certificate->cer_fundo) && Storage::disk('public')->exists($certificate->cer_fundo)) {
                Storage::disk('public')->delete($certificate->cer_fundo);
            }

            $path = $this->storeBackground($request->file('cer_fundo'), $event);
            $payload['cer_fundo'] = $path;
        }

        $certificate->update($payload);

        return back()->with('ok', 'Certificado atualizado.');
    }

    public function destroy(Event $event, Certificate $certificate)
    {
        abort_unless((int)$certificate->eve_id === (int)$event->eve_id, 404);

        if (!empty($certificate->cer_fundo) && Storage::disk('public')->exists($certificate->cer_fundo)) {
            Storage::disk('public')->delete($certificate->cer_fundo);
        }

        $certificate->delete();

        return redirect()
            ->route('admin.system.certificates.index', $event)
            ->with('ok', 'Certificado excluído.');
    }

    // -------------------------
    // Helpers
    // -------------------------

    private function validateA4(Request $request): array
    {
        $request->merge([
            'cer_espelhar' => $request->boolean('cer_espelhar'),
        ]);

        $validated = $request->validate([
            'cer_nome' => ['required', 'string', 'min:2', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:tbl_categoria,cat_id'],

            'cer_fundo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],

            // config JSON (vem como string no hidden)
            'cer_config' => ['required', 'string', 'max:200000'],
            'cer_espelhar' => ['nullable', 'boolean'],
        ], [
            'category_ids.required' => 'Selecione pelo menos 1 categoria.',
        ]);

        $cfg = json_decode($validated['cer_config'] ?? '', true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($cfg)) {
            throw ValidationException::withMessages([
                'cer_config' => 'Configuração inválida (JSON).',
            ]);
        }

        $validated['cer_config'] = $cfg;

        return $validated;
    }

    private function storeBackground($file, Event $event): string
    {
        $token = $event->eve_token ?? $event->eve_slug ?? (string)$event->eve_id;
        $folder = $token . '/certificados';
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

    private function defaultA4ConfigDeitado(): array
    {
        return [
            'page' => [
                'w' => 1920,
                'h' => 1080,
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
            return [(int)$value];
        }

        return [];
    }
}
