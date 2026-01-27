<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Credential;
use App\Support\RegistrationAudit;
use App\Models\Letter;
use Illuminate\Http\Request;

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

        $status = (string)($registration->ins_aprovado ?? '');
        $statusLabel = match ($status) {
            'S' => 'Aprovada',
            'E' => 'Em análise',
            'R' => 'Reprovada',
            'N' => 'Excluída',
            default => $status,
        };

        $credUi = [
            'enabled' => false,
            'href' => '#',
            'title' => 'Credencial',
            'desc' => 'Indisponível.',
            'class' => 'bg-zinc-950 border-zinc-800 opacity-70 cursor-not-allowed',
        ];

        // Opção 1: não aprovado => vermelho e desabilitado
        if ($status !== 'S') {
            $credUi['enabled'] = false;
            $credUi['href'] = '#';
            $credUi['desc'] = "Indisponível: status da inscrição está {$statusLabel}.";
            $credUi['class'] = 'bg-red-950/40 border-red-800 text-red-100 opacity-90 cursor-not-allowed';
            return view('public.attendee.area', compact('event', 'registration', 'credUi'));
        }

        // aprovado => busca credenciais que batem na categoria
        $catId = (int)($registration->cat_id ?? 0);

        $creds = Credential::query()
            ->where('eve_id', $event->eve_id)
            ->where('cre_tipo', 'A4')
            ->whereJsonContains('cat_id', $catId)
            ->orderByDesc('cre_id')
            ->get();

        // sem credencial: desabilita (caso raro)
        if ($creds->isEmpty()) {
            $credUi['enabled'] = false;
            $credUi['href'] = '#';
            $credUi['desc'] = 'Nenhuma credencial disponível pra sua categoria.';
            $credUi['class'] = 'bg-zinc-950 border-zinc-800 opacity-70 cursor-not-allowed';
            return view('public.attendee.area', compact('event', 'registration', 'credUi'));
        }

        // Opção 2: 1 credencial => vai direto imprimir
        if ($creds->count() === 1) {
            $credUi['enabled'] = true;
            $credUi['href'] = route('public.attendee.credentials.print', [$event, $creds->first()->cre_id]);
            $credUi['desc'] = 'Abrir e imprimir sua credencial';
            $credUi['class'] = 'bg-zinc-950 border-zinc-800 hover:border-zinc-700 transition';
            return view('public.attendee.area', compact('event', 'registration', 'credUi'));
        }

        // Opção 3: mais de 1 => vai pra escolha
        $credUi['enabled'] = true;
        $credUi['href'] = route('public.attendee.credentials.choose', $event);
        $credUi['desc'] = 'Selecione qual credencial deseja imprimir';
        $credUi['class'] = 'bg-zinc-950 border-zinc-800 hover:border-zinc-700 transition';

        return view('public.attendee.area', compact('event', 'registration', 'credUi'));
    }


    public function letter(Event $event)
    {
        $registration = $this->currentRegistration($event);

        $catId = (int)($registration->cat_id ?? 0);
        $status = (string)($registration->ins_aprovado ?? '');

        // car_trad opcional: se você tiver idioma na inscrição, use aqui
        // $lang = $registration->lang ?? null;

        $letter = Letter::query()
            ->where('eve_id', $event->eve_id)
            ->where('car_tipo', $status)
            ->whereJsonContains('cat_id', $catId)
            ->orderByDesc('car_id')
            ->first();

        // Monta nome (prioriza crachá, senão nome + sobrenome)
        $nome = trim((string)($registration->ins_nomecracha ?? ''));
        if ($nome === '') {
            $nome = trim(
                trim((string)($registration->ins_nome ?? '')) . ' ' . trim((string)($registration->ins_sobrenome ?? ''))
            );
        }

        $siteUrl = rtrim(url('/'), '/'); // http://127.0.0.1:8000
        $eventToken = $event->eve_token;
        $eventUrl = $siteUrl . "/e/{$eventToken}";

        $eventoNome = $event->eve_nome ?? $event->name ?? '';

        $nome = trim((string)($registration->ins_nomecracha ?? ''));
        if ($nome === '') {
            $nome = trim(
                trim((string)($registration->ins_nome ?? '')) . ' ' . trim((string)($registration->ins_sobrenome ?? ''))
            );
        }

        $insToken = (string)($registration->ins_token ?? ''); // aqui é o token da inscrição

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

        $form = $registration->form; // form usado na inscrição
        abort_unless($form, 404);

        $fields = $form->fields()
            ->where('fic_edita', 'S')
            ->orderBy('fic_ordem')
            ->get();

        $insDados = is_array($registration->ins_dados) ? $registration->ins_dados : [];

        $current = [];
        foreach ($fields as $field) {
            $key = $field->fic_nome;

            // prioridade: coluna física -> JSON
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

        $form = $registration->form;
        abort_unless($form, 404);

        $fields = $form->fields()
            ->where('fic_edita', 'S')
            ->orderBy('fic_ordem')
            ->get();

        // regras dinâmicas (usando padrão novo: fic_validacoes / fic_obrigatorio)
        $rules = [];
        foreach ($fields as $field) {
            $base = $field->fic_validacoes ?: ($field->fic_obrigatorio ? 'required' : 'nullable');

            $opts = $field->fic_opcoes;
            $opts = is_array($opts) ? $opts : (is_string($opts) && $opts !== '' ? json_decode($opts, true) : []);
            if (!is_array($opts)) $opts = [];

            if ($field->fic_tipo === 'multiselect' && $opts) {
                $rules["f.{$field->fic_id}"] = $field->fic_obrigatorio ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
                $rules["f.{$field->fic_id}.*"] = ['in:' . implode(',', $opts)];
            } else {
                $rules["f.{$field->fic_id}"] = $base;

                if ($field->fic_tipo === 'select' && $opts) {
                    $rules["f.{$field->fic_id}"] = [$base, 'in:' . implode(',', $opts)];
                }
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

            // normaliza
            if (is_array($incoming)) {
                $val = array_values(array_filter(array_map(fn($x) => trim((string)$x), $incoming), fn($x) => $x !== ''));
            } else {
                $txt = trim((string)($incoming ?? ''));
                $val = ($txt === '') ? null : $txt;
            }

            if (is_array($val)) {
                $before = $insDados[$key] ?? null;
                $insDados[$key] = $val;
                if ($before !== $val) {
                    $changes[$key] = ['from' => $before, 'to' => $val];
                }
                continue;
            }

            // coluna física
            if (in_array($key, $fillable, true)) {
                $before = $original[$key] ?? null;
                $registration->{$key} = $val;
                if ($before !== $val) {
                    $changes[$key] = ['from' => $before, 'to' => $val];
                }
                continue;
            }

            // fallback JSON
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
}
