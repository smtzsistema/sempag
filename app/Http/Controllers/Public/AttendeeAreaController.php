<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Credential;
use App\Models\Certificate;
use App\Support\RegistrationAudit;
use App\Models\Letter;
use App\Support\RegistrationPhoto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendeeAreaController extends Controller
{
    private function currentRegistration(Event $event): Registration
    {
        $insId = (int)session('attendee.ins_id');

        return Registration::where('ins_id', $insId)
            ->where('eve_id', $event->eve_id)
            ->with(['category', 'form.fields'])
            ->firstOrFail();
    }

    public function index(Event $event)
    {
        $registration = $this->currentRegistration($event);
        $category = $registration->category;

        $status = (string)($registration->ins_aprovado ?? '');
        $statusLabel = match ($status) {
            'S' => 'Aprovada',
            'E' => 'Em análise',
            'R' => 'Reprovada',
            'N' => 'Excluída',
            default => $status,
        };

        $registration->status = $statusLabel;

        $catId = (int)($registration->cat_id ?? 0);

        // -------------------------
        // CARD CREDENCIAL (igual teu)
        // -------------------------
        $credUi = [
            'enabled' => false,
            'href' => '#',
            'title' => 'Credencial',
            'desc' => 'Indisponível.',
            'class' => 'bg-zinc-950 border-zinc-800 opacity-70 cursor-not-allowed',
        ];

        // -------------------------
        // CARD CERTIFICADO (novo)
        // -------------------------
        $certUi = [
            'enabled' => false,
            'href' => '#',
            'title' => 'Certificado',
            'desc' => 'Indisponível.',
            'class' => 'bg-zinc-950 border-zinc-800 opacity-70 cursor-not-allowed',
        ];

        // Se não tá aprovado, os DOIS ficam travados e vermelhos
        if ($status !== 'S') {
            $msg = "Indisponível: status da inscrição está {$statusLabel}.";

            $credUi['desc'] = $msg;
            $credUi['class'] = 'bg-red-950/40 border-red-800 text-red-100 opacity-90 cursor-not-allowed';

            $certUi['desc'] = $msg;
            $certUi['class'] = 'bg-red-950/40 border-red-800 text-red-100 opacity-90 cursor-not-allowed';

            return view('public.attendee.area', compact('event', 'registration', 'credUi', 'certUi', 'category'));
        }

        // -------------------------
        // CREDENCIAL: decide fluxo
        // -------------------------
        $creds = Credential::query()
            ->where('eve_id', $event->eve_id)
            ->where('cre_tipo', 'A4')
            ->whereJsonContains('cat_id', $catId)
            ->orderByDesc('cre_id')
            ->get();

        if ($creds->isEmpty()) {
            $credUi['desc'] = 'Nenhuma credencial disponível pra sua categoria.';
            $credUi['class'] = 'bg-zinc-950 border-zinc-800 opacity-70 cursor-not-allowed';
        } elseif ($creds->count() === 1) {
            $credUi['enabled'] = true;
            $credUi['href'] = route('public.attendee.credentials.print', [$event, $creds->first()->cre_id]);
            $credUi['desc'] = 'Abrir e imprimir sua credencial';
            $credUi['class'] = 'bg-zinc-950 border-zinc-800 hover:border-zinc-700 transition';
        } else {
            $credUi['enabled'] = true;
            $credUi['href'] = route('public.attendee.credentials.choose', $event);
            $credUi['desc'] = 'Selecione qual credencial deseja imprimir';
            $credUi['class'] = 'bg-zinc-950 border-zinc-800 hover:border-zinc-700 transition';
        }

        // -------------------------
        // CERTIFICADO: decide fluxo
        // -------------------------
        $certs = Certificate::query()
            ->where('eve_id', $event->eve_id)
            ->where('cer_tipo', 'A4')
            ->whereJsonContains('cat_id', $catId)
            ->orderByDesc('cer_id')
            ->get();

        if ($certs->isEmpty()) {
            $certUi['desc'] = 'Nenhum certificado disponível pra sua categoria.';
            $certUi['class'] = 'bg-zinc-950 border-zinc-800 opacity-70 cursor-not-allowed';
        } else {
            // aqui pode sempre mandar pro entry que resolve 1 vs vários
            $certUi['enabled'] = true;
            $certUi['href'] = route('public.attendee.certificate.entry', $event);
            $certUi['desc'] = 'Abrir e imprimir seu certificado';
            $certUi['class'] = 'bg-zinc-950 border-zinc-800 hover:border-zinc-700 transition';
        }

        return view('public.attendee.area', compact('event', 'registration', 'credUi', 'certUi', 'category'));
    }

    public function letter(Event $event)
    {
        $registration = $this->currentRegistration($event);

        $category = $registration->category;

        $catId = (int)($registration->cat_id ?? 0);
        $status = (string)($registration->ins_aprovado ?? '');

        $letter = Letter::query()
            ->where('eve_id', $event->eve_id)
            ->where('car_tipo', $status)
            ->whereJsonContains('cat_id', $catId)
            ->orderByDesc('car_id')
            ->first();

        $nome = trim((string)($registration->ins_nomecracha ?? ''));
        if ($nome === '') {
            $nome = trim(
                trim((string)($registration->ins_nome ?? '')) . ' ' . trim((string)($registration->ins_sobrenome ?? ''))
            );
        }

        $siteUrl = rtrim(url('/'), '/');
        $eventToken = $event->eve_token;
        $eventUrl = $siteUrl . "/e/{$eventToken}";

        $eventoNome = $event->eve_nome ?? $event->name ?? '';

        $nome = trim((string)($registration->ins_nomecracha ?? ''));
        if ($nome === '') {
            $nome = trim(
                trim((string)($registration->ins_nome ?? '')) . ' ' . trim((string)($registration->ins_sobrenome ?? ''))
            );
        }

        $insToken = (string)($registration->ins_token ?? '');

        $letterHtml = $letter?->car_texto ?? null;
        if (!empty($letterHtml)) {
            $letterHtml = str_replace(
                ['{{nome}}', '{{URL}}', '{{SITE}}', '{{evento}}', '{{event_token}}', '{{token}}'],
                [$nome, $eventUrl, $siteUrl, $eventoNome, $eventToken, $insToken],
                $letterHtml
            );
        }

        return view('public.attendee.letter', compact('event', 'registration', 'letter', 'letterHtml'));
    }

    public function edit(Event $event)
    {
        $registration = $this->currentRegistration($event);

        $category = $registration->category;

        $form = $registration->form;
        abort_unless($form, 404);

        $fields = $form->fields()
            ->where('fic_edita', 'S')
            ->orderBy('fic_ordem')
            ->get();

        $insDados = is_array($registration->ins_dados) ? $registration->ins_dados : [];

        $current = [];
        foreach ($fields as $field) {
            $key = $field->fic_nome;

            $val = null;
            if ($key && array_key_exists($key, $registration->getAttributes())) {
                $val = $registration->{$key};
            } elseif ($key && array_key_exists($key, $insDados)) {
                $val = $insDados[$key];
            }

            $current[$field->fic_id] = old("f.{$field->fic_id}", $val);
        }

        return view('public.attendee.edit', compact('event', 'registration', 'fields', 'current'));
    }

    public function update(Request $request, Event $event)
    {
        $registration = $this->currentRegistration($event);

        $category = $registration->category;

        $form = $registration->form;
        abort_unless($form, 404);

        $fields = $form->fields()
            ->where('fic_edita', 'S')
            ->orderBy('fic_ordem')
            ->get();

        $rules = [];
        foreach ($fields as $field) {
            $base = $field->fic_validacoes ?: ($field->fic_obrigatorio ? 'required' : 'nullable');

            $baseArr = is_array($base)
                ? $base
                : array_values(array_filter(array_map('trim', explode('|', (string)$base)), fn($x) => $x !== ''));

            $opts = $field->fic_opcoes;
            $opts = is_array($opts) ? $opts : (is_string($opts) && $opts !== '' ? json_decode($opts, true) : []);
            if (!is_array($opts)) $opts = [];

            if ($field->fic_tipo === 'multiselect' && $opts) {
                $rules["f.{$field->fic_id}"] = $field->fic_obrigatorio ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
                $rules["f.{$field->fic_id}.*"] = ['in:' . implode(',', $opts)];
            } else {
                $rules["f.{$field->fic_id}"] = $baseArr;

                if ($field->fic_tipo === 'select' && $opts) {
                    $rules["f.{$field->fic_id}"] = array_merge($baseArr, ['in:' . implode(',', $opts)]);
                }
            }

            // CPF: valida dígitos + garante que não existe em OUTRA inscrição do mesmo evento
            $isCpf = ($field->fic_tipo === 'cpf') || in_array((string)$field->fic_nome, ['ins_cpf', 'cpf'], true);
            if ($isCpf) {
                $rules["f.{$field->fic_id}"][] = function (string $attribute, $value, $fail) use ($event, $registration) {
                    $digits = preg_replace('/\D+/', '', (string)$value);
                    if ($digits === '') return;

                    if (strlen($digits) !== 11 || !$this->isValidCpfDigits($digits)) {
                        $fail('CPF inválido.');
                        return;
                    }

                    $exists = Registration::where('eve_id', $event->eve_id)
                        ->where('ins_id', '!=', $registration->ins_id)
                        ->whereRaw(
                            "REPLACE(REPLACE(REPLACE(REPLACE(ins_cpf,'.',''),'-',''),' ',''),'/','') = ?",
                            [$digits]
                        )
                        ->exists();

                    if ($exists) {
                        $fail('Esse CPF já está cadastrado em outra inscrição.');
                    }
                };
            }

            // Email: evita dois inscritos com o mesmo e-mail no evento
            $isEmail = ($field->fic_tipo === 'email') || in_array((string)$field->fic_nome, ['ins_email', 'email'], true);
            if ($isEmail) {
                $rules["f.{$field->fic_id}"][] = Rule::unique('tbl_inscricao', 'ins_email')
                    ->where(fn($q) => $q->where('eve_id', $event->eve_id))
                    ->ignore($registration->ins_id, 'ins_id');
            }

            $isMobile = in_array((string)$field->fic_tipo, ['mobile_int', 'celular_int', 'celular'], true)
                || in_array((string)$field->fic_nome, ['ins_tel_celular', 'ins_celular', 'ins_whatsapp', 'ins_mobile'], true);

            if ($isMobile) {
                $rules["f.{$field->fic_id}"][] = function ($attribute, $value, $fail) {
                    $digits = preg_replace('/\D+/', '', (string)$value);
                    if ($digits === '') return;

                    $len = strlen($digits);
                    if ($len < 10 || $len > 15) {
                        $fail('Celular inválido. Use +<código><número>.');
                        return;
                    }

                    // regra extra BR
                    if (str_starts_with($digits, '55')) {
                        // celular BR: 13 dígitos e o 5º dígito (index 4) é 9
                        if (!($len === 13 && ($digits[4] ?? '') === '9')) {
                            $fail('Celular BR inválido. Ex: +55 11 91234-5678');
                        }
                    }
                };
            }

            $isPhone = in_array((string)$field->fic_tipo, ['phone_int', 'telefone_int', 'telefone', 'phone'], true)
                || in_array((string)$field->fic_nome, ['ins_tel_comercial', 'ins_telefone', 'ins_fone', 'ins_phone'], true);

            if ($isPhone) {
                $rules["f.{$field->fic_id}"][] = function ($attribute, $value, $fail) {
                    $digits = preg_replace('/\D+/', '', (string)$value);
                    if ($digits === '') return;

                    $len = strlen($digits);
                    if ($len < 10 || $len > 15) {
                        $fail('Telefone inválido. Use +<código><número>.');
                        return;
                    }

                    // regra extra BR
                    if (str_starts_with($digits, '55')) {
                        // fixo BR: 12 dígitos e o 5º dígito (index 4) NÃO é 9
                        if (!($len === 12 && ($digits[4] ?? '') !== '9')) {
                            $fail('Telefone BR inválido. Ex: +55 11 1234-5678');
                        }
                    }
                };
            }
        }


        $validated = $request->validate($rules);
        $payload = $validated['f'] ?? [];
        if (!is_array($payload)) $payload = [];

        $original = $registration->getOriginal();

        $fillable = $registration->getFillable();
        $insDados = is_array($registration->ins_dados) ? $registration->ins_dados : [];
        if (!is_array($insDados)) $insDados = [];

        $changes = [];

        foreach ($fields as $field) {
            $key = $field->fic_nome;
            if (!$key) continue;

            $incoming = $payload[$field->fic_id] ?? null;

            if (is_array($incoming)) {
                $val = array_values(array_filter(array_map(fn($x) => trim((string)$x), $incoming), fn($x) => $x !== ''));
            } else {
                $txt = trim((string)($incoming ?? ''));
                $val = ($txt === '') ? null : $txt;

                if (is_string($val) && in_array($key, ['ins_tel_celular', 'ins_tel_comercial'], true)) {
                    $digits = preg_replace('/\D+/', '', $val);
                    $val = $digits ? ('+' . $digits) : null;
                }

            }

            if (is_array($val)) {
                $before = $insDados[$key] ?? null;
                $insDados[$key] = $val;
                if ($before !== $val) {
                    $changes[$key] = ['from' => $before, 'to' => $val];
                }
                continue;
            }

            if (in_array($key, $fillable, true)) {
                $before = $original[$key] ?? null;
                $registration->{$key} = $val;
                if ($before !== $val) {
                    $changes[$key] = ['from' => $before, 'to' => $val];
                }
                continue;
            }

            $before = $insDados[$key] ?? null;
            if ($val === null) {
                unset($insDados[$key]);
            } else {
                $insDados[$key] = $val;
            }
            if ($before !== ($insDados[$key] ?? null)) {
                $changes[$key] = ['from' => $before, 'to' => ($insDados[$key] ?? null)];
            }
        }

        $registration->ins_dados = $insDados ?: null;
        $registration->save();

        if (!empty($changes)) {
            RegistrationAudit::log($registration, $request, 'attendee', null, $changes);
        }

        return redirect()
            ->route('public.attendee.edit', $event)
            ->with('ok', 'Dados atualizados com sucesso!');
    }

    private function isValidCpfDigits(string $cpfDigits): bool
    {
        if (strlen($cpfDigits) !== 11) return false;
        if (preg_match('/^(\d)\1{10}$/', $cpfDigits)) return false;

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int)$cpfDigits[$i]) * (10 - $i);
        }
        $d1 = ($sum * 10) % 11;
        if ($d1 === 10) $d1 = 0;
        if ($d1 !== (int)$cpfDigits[9]) return false;

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int)$cpfDigits[$i]) * (11 - $i);
        }
        $d2 = ($sum * 10) % 11;
        if ($d2 === 10) $d2 = 0;
        return $d2 === (int)$cpfDigits[10];
    }


    public function photo(Event $event)
    {
        $registration = $this->currentRegistration($event);

        $category = $registration->category;
        abort_unless($registration->form?->photoEnabled(), 404);

        $category = $registration->category;

        return view('public.attendee.photo', compact('event', 'registration', 'category'));
    }

    public function photoUpdate(Request $request, Event $event)
    {
        $registration = $this->currentRegistration($event);

        $category = $registration->category;
        abort_unless($registration->form?->photoEnabled(), 404);

        $data = $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        RegistrationPhoto::store($event, $registration, $request->file('photo'));

        return back()->with('ok', 'Foto atualizada.');
    }

    public function photoDestroy(Event $event)
    {
        $registration = $this->currentRegistration($event);

        $category = $registration->category;
        abort_unless($registration->form?->photoEnabled(), 404);

        RegistrationPhoto::deactivateAll($registration);

        return back()->with('ok', 'Foto removida.');
    }

    // -----------------
// Contestação (inscrito)
// -----------------
    public function contest(Request $request, Event $event)
    {
        $registration = $this->currentRegistration($event);

        // Só deixa contestar quando estiver reprovada.
        if ((string)($registration->ins_aprovado ?? '') !== 'R') {
            return back()->with('error', 'Contestação disponível apenas quando a inscrição estiver reprovada.');
        }

        $data = $request->validate([
            'ins_contesta' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'ins_contesta.required' => 'Escreve a contestação aí.',
            'ins_contesta.min' => 'Coloca pelo menos :min caracteres pra ficar claro.',
            'ins_contesta.max' => 'A contestação ficou grande demais (máx :max caracteres).',
        ]);

        $registration->ins_contesta = trim((string)$data['ins_contesta']);
        $registration->save();

        return back()->with('ok', 'Contestação enviada.');
    }


}
