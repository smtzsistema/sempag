<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Models\Organizer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminEventAuth
{
    public function handle(Request $request, Closure $next)
    {
        $eventParam = $request->route('event');

        // Resolve {event} caso venha como string (ex: "demo2025")
        $event = $eventParam instanceof Event
            ? $eventParam
            : Event::where('eve_token', $eventParam)->first();

        if (!$event) {
            abort(404);
        }

        if (!Auth::check()) {
            return redirect()->route('admin.login', $event);
        }

        // Garante que o usuário logado é dono da organizadora do evento
        $organizer = Organizer::find($event->organizer_id);

        $isOwner = $organizer && (int) $organizer->user_id === (int) Auth::id();

        // Dono do evento sempre deve ter acesso total (garante role Admin)
        if ($isOwner) {
            try {
                $u = Auth::user();
                if ($u && method_exists($u, 'assignRole') && !$u->hasRole('Admin')) {
                    $u->assignRole('Admin');
                }
            } catch (\Throwable $e) {
                // ignora se Spatie não estiver instalado ainda
            }
        }

        // Ou então é um usuário vinculado ao evento (tbl_evento_usuarios)
        $isMember = $event->users()
            ->where('tbl_usuarios.usu_id', Auth::id())
            ->exists();

        if (!$isOwner && !$isMember) {
            abort(403, 'Sem permissão para acessar este evento.');
        }

        return $next($request);
    }
}
