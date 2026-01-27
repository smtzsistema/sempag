<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventPublicController extends Controller
{
    public function show(Event $event)
    {
        // $event vem por token se o Event model tiver getRouteKeyName()
        $event->load(['categories' => function ($q) {
            $q->where('cat_ativo', true)
                ->where(function ($qq) {
                    $qq->whereNull('cat_date_start')->orWhere('cat_date_start', '<=', now());
                })
                ->where(function ($qq) {
                    $qq->whereNull('cat_date_end')->orWhere('cat_date_end', '>=', now());
                })
                ->orderBy('cat_id');
        }]);

        return view('public.event.show', compact('event'));
    }

    public function landing(Event $event)
    {
        return view('public.event.landing', compact('event'));
    }
}
