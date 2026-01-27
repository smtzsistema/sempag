<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function loginForm(Event $event)
    {
        // Se já estiver logado, tenta ir direto pro painel
        if (Auth::check()) {
            $organizer = Organizer::find($event->organizer_id);
            $isOwner = $organizer && (int) $organizer->user_id === (int) Auth::id();

            $isMember = $event->users()
                ->where('tbl_usuarios.usu_id', Auth::id())
                ->exists();

            if ($isOwner || $isMember) {
                return redirect()->route('admin.dashboard', $event);
            }

            // Logado, mas sem permissão nesse evento
            Auth::logout();
        }

        return view('admin.auth.login', compact('event'));
    }

    public function login(Request $request, Event $event)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'usu_email' => $data['email'],
            'password'  => $data['password'],
        ];

        if (!Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $request->session()->regenerate();

        // Checa permissão no evento
        $organizer = Organizer::find($event->organizer_id);
        $isOwner = $organizer && (int) $organizer->user_id === (int) Auth::id();

        $isMember = $event->users()
            ->where('tbl_usuarios.usu_id', Auth::id())
            ->exists();

        if (!$isOwner && !$isMember) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Seu usuário não tem acesso a este evento.'],
            ]);
        }

        return redirect()->route('admin.dashboard', $event);
    }

    public function logout(Request $request, Event $event)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login', $event);
    }
}
