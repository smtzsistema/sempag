<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class LetterTemplateController extends Controller
{
    public function edit(Event $event)
    {
        $settings = $event->settings ?? [];

        $subject = $settings['confirmation_subject'] ?? "Confirmação de inscrição - {$event->name}";

        $html = $settings['confirmation_html'] ?? "<h1>Inscrição confirmada</h1>\n<p>Olá, {{full_name}}!</p>\n<p>Sua inscrição para <strong>{{event_name}}</strong> foi registrada.</p>\n<p><strong>Token:</strong> {{token}}</p>\n<p>Guarde este e-mail para futuras consultas.</p>";

        return view('admin.system.letters.edit', compact('event', 'subject', 'html'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'html' => ['required', 'string', 'min:10', 'max:200000'],
        ]);

        $settings = $event->settings ?? [];
        $settings['confirmation_subject'] = $data['subject'];
        $settings['confirmation_html'] = $data['html'];

        $event->update(['settings' => $settings]);

        return back()->with('ok', 'Modelo de carta salvo.');
    }
}
