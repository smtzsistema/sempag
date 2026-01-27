<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    protected $table = 'tbl_credencial';
    protected $primaryKey = 'cre_id';

    protected $fillable = [
        'eve_id',
        'cre_nome',
        'cre_tipo',
        'cat_id',
        'cre_fundo',
        'cre_espelhar',
        'cre_config',
    ];

    protected $casts = [
        'cat_id' => 'array',
        'cre_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eve_id', 'eve_id');
    }

    public function getMirrorAttribute(): bool
    {
        return ($this->attributes['cre_espelhar'] ?? 'N') === 'S';
    }

    public function setMirrorAttribute($value): void
    {
        $this->attributes['cre_espelhar'] = ((bool) $value) ? 'S' : 'N';
    }
}
