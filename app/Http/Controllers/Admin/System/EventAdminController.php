<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventAdminController extends Controller
{
    public function index(Event $event)
    {
        return view('admin.event.index', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('admin.event.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'eve_nome' => ['required', 'string', 'max:255'],
            'eve_slug' => ['nullable', 'string', 'max:255'],
            'eve_descricao' => ['nullable', 'string'], // só se existir no banco
            'eve_data_inicio' => ['nullable', 'date'],
            'eve_data_fim' => ['nullable', 'date', 'after_or_equal:eve_data_inicio'],

            // upload
            'eve_banner' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('eve_banner')) {
            $file = $request->file('eve_banner');

            $token = $event->eve_token ?? $event->eve_slug ?? (string)$event->eve_id;
            $folder = $token . '/banner';
            $filename = $file->hashName();

            if (!empty($event->eve_banner) && Storage::disk('public')->exists($event->eve_banner)) {
                Storage::disk('public')->delete($event->eve_banner);
            }

            $path = $file->storeAs($folder, $filename, 'public');

            $data['eve_banner'] = $path;
        }

        $event->update($data);

        return back()->with('ok', 'Evento atualizado.');
    }

}
