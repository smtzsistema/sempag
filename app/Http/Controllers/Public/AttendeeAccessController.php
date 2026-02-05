<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AttendeeAccessController extends Controller
{
    public function form(Event $event)
    {
        return view('public.attendee.forgot', compact('event'));
    }

    public function send(Request $request, Event $event)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
        ]);

        $login = trim($data['login']);
        $isEmail = str_contains($login, '@');
        $cpfDigits = preg_replace('/\D+/', '', $login);

        $eveId = (int)($event->eve_id ?? $event->id ?? 0);
        $orgId = (int)($event->org_id ?? 0);

        $registration = Registration::query()
            ->where('eve_id', $eveId)
            ->when($isEmail, fn($q) => $q->where('ins_email', $login))
            ->when(!$isEmail, fn($q) => $q->whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(ins_cpf,'.',''),'-',''),' ',''),'/','') = ?",
                [$cpfDigits]
            ))
            ->orderByDesc('ins_id')
            ->first();

        $msg = 'Se existir uma inscrição com esse dado, enviaremos um link para redefinir sua senha.';

        if (!$registration || !$registration->email) {
            return back()->with('ok', $msg);
        }

        $rt = Str::random(64);

        $signedUrl = URL::temporarySignedRoute(
            'public.attendee.reset.form',
            now()->addMinutes(30),
            [$event, $registration, 'rt' => $rt]
        );

        parse_str((string)parse_url($signedUrl, PHP_URL_QUERY), $qs);

        $tokenToStore = http_build_query([
            'expires' => $qs['expires'] ?? null,
            'rt' => $qs['rt'] ?? null,
            'signature' => $qs['signature'] ?? null,
        ], '', '&', PHP_QUERY_RFC3986);

        DB::table('password_reset_tokens')->insert([
            'org_id' => $orgId ?: null,
            'eve_id' => $eveId ?: null,
            'email' => $registration->email,
            'token' => $tokenToStore,
            'created_at' => now(),
            'clicked_at' => null,
            'used_at' => null,
            'ip' => null,
            'user_agent' => null,
        ]);

        Mail::raw(
            "Redefinição de senha (válido por 30 minutos):\n\n{$signedUrl}",
            function ($m) use ($registration, $event) {
                $m->to($registration->email)
                  ->subject("Redefinir senha - {$event->name}");
            }
        );

        return back()->with('ok', $msg);
    }

    /**
     * Valida o token do reset e devolve a linha do histórico.
     * Se já estiver usado, redireciona pro login com mensagem.
     */
    private function assertResetToken(Event $event, Registration $registration, string $rt, Request $request)
    {
        if ($rt === '') abort(403);

        $eveId = (int)($event->eve_id ?? $event->id ?? 0);
        $orgId = (int)($event->org_id ?? 0);

        $row = DB::table('password_reset_tokens')
            ->where('email', $registration->email)
            ->where('eve_id', $eveId ?: null)
            ->where('org_id', $orgId ?: null)
            ->orderByDesc('created_at')
            ->first();

        if (!$row) abort(403);

        // Se já usou, manda pro login
        if (!empty($row->used_at)) {
            return redirect()
                ->route('public.attendee.login', $event)
                ->with('error', 'Token já utilizado.');
        }

        parse_str((string)$row->token, $saved);

        if (($saved['rt'] ?? '') !== $rt) abort(403);

        $exp = (int)($saved['expires'] ?? 0);
        if ($exp > 0 && now()->timestamp > $exp) abort(403);

        return $row;
    }

    public function resetForm(Request $request, Event $event, Registration $registration)
    {
        abort_unless($registration->event_id === (int)($event->eve_id ?? $event->id), 404);

        $rt = (string)$request->query('rt', '');
        $row = $this->assertResetToken($event, $registration, $rt, $request);

        // se assertResetToken devolveu redirect, retorna ele
        if ($row instanceof \Illuminate\Http\RedirectResponse) {
            return $row;
        }

        if (empty($row->clicked_at)) {
            DB::table('password_reset_tokens')
                ->where('prt_id', $row->prt_id)
                ->update([
                    'clicked_at' => now(),
                    'ip' => $request->ip(),
                    'user_agent' => substr((string)$request->userAgent(), 0, 2000),
                ]);
        }

        return view('public.attendee.reset', compact('event', 'registration'));
    }

    public function resetStore(Request $request, Event $event, Registration $registration)
    {
        abort_unless($registration->event_id === (int)($event->eve_id ?? $event->id), 404);

        $rt = (string)$request->query('rt', '');
        $row = $this->assertResetToken($event, $registration, $rt, $request);

        // se assertResetToken devolveu redirect, retorna ele
        if ($row instanceof \Illuminate\Http\RedirectResponse) {
            return $row;
        }

        $data = $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $registration->update([
            'password' => Hash::make($data['password']),
        ]);

        DB::table('password_reset_tokens')
            ->where('prt_id', $row->prt_id)
            ->update([
                'used_at' => now(),
            ]);

        return redirect()
            ->route('public.attendee.login', $event)
            ->with('ok', 'Senha redefinida com sucesso. Agora faça login.');
    }
}
