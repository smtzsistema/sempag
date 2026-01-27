<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AttendeeAuthController extends Controller
{
    public function loginForm(Event $event)
    {
        return view('public.attendee.login', compact('event'));
    }

   public function login(Request $request, Event $event)
   {
       $data = $request->validate([
           'login_type' => ['required','in:email,cpf'],
           'login'      => ['required','string'],
           'password'   => ['required'],
       ]);

       $login = trim($data['login']);
       $type  = $data['login_type'];
       $query = Registration::query()->where('eve_id', $event->id);

       if ($type === 'cpf') {
           $cpfDigits = preg_replace('/\D+/', '', $login);

           // garante que tem 11 dígitos (opcional mas recomendado)
           if (strlen($cpfDigits) !== 11) {
               return back()->withErrors(['login' => 'CPF inválido.'])->withInput();
           }

           // funciona se o banco tiver com máscara OU sem máscara
           $query->whereRaw(
               "REPLACE(REPLACE(REPLACE(REPLACE(ins_cpf,'.',''),'-',''),' ',''),'/','') = ?",
               [$cpfDigits]
           );
       } else {
           $query->where('ins_email', $login);
       }

       $registration = $query->orderByDesc('ins_id')->first();

       if (!$registration || !$registration->password || !password_verify($data['password'], $registration->password)) {
           return back()->withErrors(['login' => 'Login ou senha inválidos.'])->withInput();
       }

       session([
           'attendee.registration_id' => $registration->id,
           'attendee.event_id' => $event->id,
       ]);

       return redirect()->route('public.attendee.area', $event);
   }



    public function logout(Event $event)
    {
        session()->forget(['attendee.event_id', 'attendee.registration_id']);
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('public.event.landing', $event);
    }
}
