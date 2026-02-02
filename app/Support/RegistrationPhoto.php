<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Gallery;
use App\Models\Registration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RegistrationPhoto
{
    /**
     * Salva uma nova foto e marca as antigas como inativas.
     * Retorna o registro criado (tbl_galeria).
     */
    public static function store(Event $event, Registration $registration, UploadedFile $file): Gallery
    {
        // 1) desativa todas as fotos anteriores desse inscrito
        Gallery::where('ins_id', $registration->ins_id)->where('gal_ativo', 1)->update([
            'gal_ativo' => 0,
            'gal_atualizado' => 'S',
            'gal_date_status' => now(),
        ]);

        // 2) salva arquivo no disco public
        $eventToken = trim((string)($event->eve_token ?? ''));
        if ($eventToken === '') {
            $eventToken = 'event_' . (int)$event->eve_id;
        }

        // padrão: /storage/{eve_token}/uploads/{eve_id}/arquivo.ext
        $folder = $eventToken . '/uploads/' . (int)$event->eve_id;
        $filename = $file->hashName(); // inclui extensao
        $path = $file->storeAs($folder, $filename, 'public');

        // 3) cria registro
        $g = new Gallery();
        $g->fill([
            'ins_id' => $registration->ins_id,
            'gal_url' => $path,
            'gal_date' => now(),
            'gal_status' => 0,
            'gal_ativo' => 1,
            'gal_rotate' => null,
            'gal_date_status' => null,
            'gal_atualizado' => 'S',
            'gal_local' => 'N',
        ]);
        $g->save();

        return $g;
    }

    /**
     * "Exclui" foto: só marca gal_ativo = 0.
     * Por padrão, desativa TODAS as fotos do inscrito, pra não voltar pra uma antiga.
     */
    public static function deactivateAll(Registration $registration): void
    {
        Gallery::where('ins_id', $registration->ins_id)->where('gal_ativo', 1)->update([
            'gal_ativo' => 0,
            'gal_atualizado' => 'S',
            'gal_date_status' => now(),
        ]);
    }

    /**
     * Retorna a URL pública (storage) da foto ativa mais recente.
     */
    public static function activeUrl(Registration $registration): ?string
    {
        $g = Gallery::where('ins_id', $registration->ins_id)
            ->where('gal_ativo', 1)
            ->orderByDesc('gal_date')
            ->orderByDesc('gal_id')
            ->first();

        if (!$g) return null;
        if (empty($g->gal_url)) return null;

        return Storage::disk('public')->url($g->gal_url);
    }
}
