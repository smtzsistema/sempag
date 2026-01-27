<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;

class AttendeeAuth
{
    public function handle(Request $request, Closure $next)
    {
        $eventParam = $request->route('event');

        // Se vier string (ex: "demo2025"), resolve para model
        $event = $eventParam instanceof Event
            ? $eventParam
            : Event::where('eve_token', $eventParam)->first();

        if (!$event) {
            abort(404);
        }

        // PADRÃO NOVO (sessão): attendee.ins_id / attendee.eve_id
        if (!session('attendee.ins_id') || (int) session('attendee.eve_id') !== (int) $event->eve_id) {
            return redirect()->route('public.attendee.login', $event);
        }

        return $next($request);
    }
}
