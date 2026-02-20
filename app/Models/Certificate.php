<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = 'tbl_certificado';
    protected $primaryKey = 'cer_id';

    protected $fillable = [
        'eve_id',
        'cer_nome',
        'cer_tipo',
        'cat_id',
        'cer_fundo',
        'cer_espelhar',
        'cer_config',
    ];

    protected $casts = [
        'cat_id' => 'array',
        'cer_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eve_id', 'eve_id');
    }

    public function getMirrorAttribute(): bool
    {
        return ($this->attributes['cer_espelhar'] ?? 'N') === 'S';
    }

    public function setMirrorAttribute($value): void
    {
        $this->attributes['cer_espelhar'] = ((bool)$value) ? 'S' : 'N';
    }
}
