<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

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

        $registration = Registration::query()
            ->where('eve_id', $event->id)
            ->when($isEmail, fn($q) => $q->where('ins_email', $login))
            ->when(!$isEmail, fn($q) => $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(ins_cpf,'.',''),'-',''),' ',''),'/','') = ?", [$cpfDigits]))
            ->orderByDesc('ins_id')
            ->first();

        // Mensagem genérica sempre (não revela se existe)
        $msg = 'Se existir uma inscrição com esse dado, enviaremos um link para redefinir sua senha.';

        if (!$registration || !$registration->email) {
            return back()->with('ok', $msg);
        }

        // Link assinado e temporário
        $signedUrl = URL::temporarySignedRoute(
            'public.attendee.reset.form',
            now()->addMinutes(30),
            [$event, $registration] // aqui ele usa o token por causa do :token na rota
        );

        Mail::raw(
            "Redefinição de senha (válido por 30 minutos):\n\n{$signedUrl}",
            function ($m) use ($registration, $event) {
                $m->to($registration->email)
                  ->subject("Redefinir senha - {$event->name}");
            }
        );

        return back()->with('ok', $msg);
    }

    public function resetForm(Event $event, Registration $registration)
    {
        abort_unless($registration->event_id === $event->id, 404);

        return view('public.attendee.reset', compact('event', 'registration'));
    }

    public function resetStore(Request $request, Event $event, Registration $registration)
    {
        abort_unless($registration->event_id === $event->id, 404);

        $data = $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $registration->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('public.attendee.login', $event)
            ->with('ok', 'Senha redefinida com sucesso. Agora faça login.');
    }
}
