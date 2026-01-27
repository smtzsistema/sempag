<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Credential;

class Event extends Model
{
    protected $table = 'tbl_eventos';
    protected $primaryKey = 'eve_id';

    protected $fillable = [
        'org_id',
        'eve_nome',
        'eve_descricao',
        'eve_data_inicio',
        'eve_data_fim',
        'eve_local',
        'eve_banner',
        'eve_fundo',
        'eve_token',
        'eve_slug',
        'eve_settings',
    ];

    protected $casts = [
        'eve_settings' => 'array',
        'eve_data_inicio' => 'datetime',
        'eve_data_fim' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        // coluna real no banco
        return 'eve_token';
    }

    // -----------------
    // Relacionamentos
    // -----------------
    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'org_id', 'org_id');
    }

    public function credentials()
    {
        return $this->hasMany(Credential::class, 'eve_id', 'eve_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'eve_id', 'eve_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'eve_id', 'eve_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tbl_evento_usuarios', 'eve_id', 'usu_id')
            ->withTimestamps();
    }

    // -----------------
    // Acessors/Mutators (API "amigável" no código)
    // -----------------
    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function getOrganizerIdAttribute()
    {
        return $this->attributes['org_id'] ?? null;
    }
    public function setOrganizerIdAttribute($value): void
    {
        $this->attributes['org_id'] = $value;
    }

    public function getNameAttribute()
    {
        return $this->attributes['eve_nome'] ?? null;
    }
    public function setNameAttribute($value): void
    {
        $this->attributes['eve_nome'] = $value;
    }

    public function getSlugAttribute()
    {
        return $this->attributes['eve_slug'] ?? null;
    }
    public function setSlugAttribute($value): void
    {
        $this->attributes['eve_slug'] = $value;
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['eve_descricao'] ?? null;
    }
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['eve_descricao'] = $value;
    }

    public function getTokenAttribute()
    {
        return $this->attributes['eve_token'] ?? null;
    }
    public function setTokenAttribute($value): void
    {
        $this->attributes['eve_token'] = $value;
    }

    public function getDateStartAttribute()
    {
        return $this->attributes['eve_data_inicio'] ?? null;
    }
    public function setDateStartAttribute($value): void
    {
        $this->attributes['eve_data_inicio'] = $value;
    }

    public function getDateEndAttribute()
    {
        return $this->attributes['eve_data_fim'] ?? null;
    }
    public function setDateEndAttribute($value): void
    {
        $this->attributes['eve_data_fim'] = $value;
    }

    public function getLocationAttribute()
    {
        return $this->attributes['eve_local'] ?? null;
    }
    public function setLocationAttribute($value): void
    {
        $this->attributes['eve_local'] = $value;
    }

    public function getBannerPathAttribute()
    {
        return $this->attributes['eve_banner'] ?? null;
    }
    public function setBannerPathAttribute($value): void
    {
        $this->attributes['eve_banner'] = $value;
    }

    public function getBackgroundPathAttribute()
    {
        return $this->attributes['eve_fundo'] ?? null;
    }
    public function setBackgroundPathAttribute($value): void
    {
        $this->attributes['eve_fundo'] = $value;
    }

    public function getSettingsAttribute()
    {
        return $this->attributes['eve_settings'] ?? null;
    }
    public function setSettingsAttribute($value): void
    {
        $this->attributes['eve_settings'] = $value;
    }
}
