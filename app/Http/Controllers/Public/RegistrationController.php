<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Form;
use App\Models\Registration;
use App\Support\RegistrationPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function create(Event $event, Category $category)
    {
        abort_unless((int)$category->eve_id === (int)$event->eve_id, 404);

        // categoria precisa estar ativa e dentro da janela de visibilidade (se configurada)
        abort_unless((bool)$category->cat_ativo, 404);
        if (!empty($category->cat_date_start) && now()->lt($category->cat_date_start)) abort(404);
        if (!empty($category->cat_date_end) && now()->gt($category->cat_date_end)) abort(404);

        $form = Form::where('eve_id', $event->eve_id)
            ->where('cat_id', $category->cat_id)
            ->where('form_ativo', true)
            ->orderByDesc('form_versao')
            ->with('fields')
            ->firstOrFail();

        return view('public.registration.form', compact('event', 'category', 'form'));
    }

    public function store(Request $request, Event $event, Category $category)
    {
        abort_unless((int)$category->eve_id === (int)$event->eve_id, 404);

        abort_unless((bool)$category->cat_ativo, 404);
        if (!empty($category->cat_date_start) && now()->lt($category->cat_date_start)) abort(404);
        if (!empty($category->cat_date_end) && now()->gt($category->cat_date_end)) abort(404);

        $form = Form::where('eve_id', $event->eve_id)
            ->where('cat_id', $category->cat_id)
            ->where('form_ativo', true)
            ->orderByDesc('form_versao')
            ->with('fields')
            ->firstOrFail();

        // 1) valida senha (campo do form deve ser "password" e "password_confirmation")
        $validatedPassword = $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        // Foto (módulo do formulário)
        $photoRules = $form->photoEnabled()
            ? ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:10240']
            : ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'];

        $request->validate([
            'photo' => $photoRules,
        ]);

        // 2) regras dinâmicas pros campos do form
        $rules = [];
        foreach ($form->fields as $field) {
            $key = "f.{$field->fic_id}";

            $rules[$key] = !empty($field->fic_validacoes)
                ? $field->fic_validacoes
                : ($field->fic_obrigatorio ? 'required' : 'nullable');
        }

        $validated = $request->validate($rules);

        // 3) extrai core fields (nome/email/cpf etc) a partir das respostas
        $core = $this->extractCore($form, $validated['f'] ?? []);

        // fallback pros fixos do layout (se algum vier fora do f[fic_id])
        $core['ins_email'] = $core['ins_email'] ?? $request->input('ins_email');
        $core['ins_cpf'] = $core['ins_cpf'] ?? $request->input('ins_cpf');
        $core['ins_nome'] = $core['ins_nome'] ?? $request->input('ins_nome');
        $core['ins_sobrenome'] = $core['ins_sobrenome'] ?? $request->input('ins_sobrenome');
        $core['ins_nomecracha'] = $core['ins_nomecracha'] ?? $request->input('ins_nomecracha');
        $core['ins_instituicao'] = $core['ins_instituicao'] ?? $request->input('ins_instituicao');
        $core['ins_cargo'] = $core['ins_cargo'] ?? $request->input('ins_cargo');

        // 3.1) duplicidade (evento)
        $email = $core['ins_email'] ?? null;
        $cpf = isset($core['ins_cpf']) ? preg_replace('/\D+/', '', (string)$core['ins_cpf']) : null;

        $exists = Registration::where('eve_id', $event->eve_id)
            ->where(function ($q) use ($email, $cpf) {
                if ($email) $q->orWhere('ins_email', $email);

                // fallback compatível (não depende de REGEXP_REPLACE)
                if ($cpf) {
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(ins_cpf,'.',''),'-',''),' ',''),'/','') = ?",
                        [$cpf]
                    );
                }
            })
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('already_registered', true)
                ->with('login_prefill', $cpf ?: $email)
                ->withErrors(['ins_email' => 'Você já possui inscrição com esse dado.']);
        }

        // ==========================================================
        // 4) monta payload COMPLETO usando SOMENTE tbl_inscricao
        // - se o fic_nome existir como coluna (ins_*), salva na coluna
        // - caso contrário, salva dentro de ins_dados (JSON)
        // ==========================================================
        $registrationTmp = new Registration();
        $fillable = $registrationTmp->getFillable();

        $insDados = is_array($request->input('ins_dados')) ? $request->input('ins_dados') : [];
        if (!is_array($insDados)) $insDados = [];

        $payload = [
            'eve_id' => $event->eve_id,
            'cat_id' => $category->cat_id,
            'form_id' => $form->form_id,
            'usu_id' => null,

            'ins_token' => (string)Str::uuid(),
            'ins_aprovado' => $category->cat_aprova ? 'E' : 'S',
            'ins_senha' => Hash::make($validatedPassword['password']),
        ];

        // primeiro, aplica os core
        foreach ($core as $k => $v) {
            if (!is_string($k) || $k === '') continue;

            if (in_array($k, $fillable, true)) {
                $payload[$k] = is_array($v) ? null : (is_null($v) ? null : (string)$v);
            } else {
                $insDados[$k] = $v;
            }
        }

        // depois, percorre TODOS os campos do form e joga no lugar certo
        foreach ($form->fields as $field) {
            $k = $field->fic_nome;
            if (!$k) continue;

            $incoming = $validated['f'][$field->fic_id] ?? null;

            $isColumn = in_array($k, $fillable, true);

            // normaliza
            if (is_array($incoming)) {
                // multiselect: vira string "A; B; C"
                $arr = array_values(array_filter(
                    array_map(fn($x) => trim((string)$x), $incoming),
                    fn($x) => $x !== ''
                ));

                $val = !empty($arr) ? implode('; ', $arr) : null;
            } else {
                $txt = trim((string)($incoming ?? ''));
                $val = ($txt === '') ? null : $txt;
            }

            // se for coluna física, salva nela
            if ($isColumn) {
                $payload[$k] = $val;
                continue;
            }

            // fallback: vai pro JSON (como string também)
            $insDados[$k] = $val;
        }


        // se tiver algo no JSON, salva
        if (!empty($insDados)) {
            $payload['ins_dados'] = $insDados;
        }

        // cria inscrição (tbl_inscricao) com payload completo (bypass fillable)
        $registration = new Registration();
        $registration->forceFill($payload);
        $registration->save();

        // Foto obrigatória quando o módulo está ativo
        if ($form->photoEnabled() && $request->hasFile('photo')) {
            RegistrationPhoto::store($event, $registration, $request->file('photo'));
        }

        // 6) gera e salva carta de confirmação (HTML) no próprio registro
        $subject = "Confirmação de inscrição - {$event->eve_nome}";
        $html = view('emails.registration_confirmation', [
            'event' => $event,
            'registration' => $registration,
        ])->render();

        $registration->update([
            'ins_confirmacao_assunto' => $subject,
            'ins_confirmacao_html' => $html,
        ]);

        session([
            'attendee.ins_id' => $registration->ins_id,
            'attendee.eve_id' => $event->eve_id,
        ]);

        return redirect()
            ->route('public.attendee.letter', $event)
            ->with('ok', 'Inscrição enviada com sucesso!');
    }

    private function extractCore(Form $form, array $answersByFieldId): array
    {
        $byKey = [];
        foreach ($form->fields as $field) {
            $byKey[$field->fic_nome] = $answersByFieldId[$field->fic_id] ?? null;
        }

        return [
            'ins_nome' => $byKey['ins_nome'] ?? $byKey['nome'] ?? null,
            'ins_sobrenome' => $byKey['ins_sobrenome'] ?? $byKey['sobrenome'] ?? null,
            'ins_nomecracha' => $byKey['ins_nomecracha'] ?? null,

            'ins_email' => $byKey['ins_email'] ?? $byKey['email'] ?? null,
            'ins_cpf' => $byKey['ins_cpf'] ?? $byKey['cpf'] ?? null,

            'ins_instituicao' => $byKey['ins_instituicao'] ?? $byKey['empresa'] ?? null,
            'ins_cargo' => $byKey['ins_cargo'] ?? $byKey['cargo'] ?? null,
        ];
    }
}
