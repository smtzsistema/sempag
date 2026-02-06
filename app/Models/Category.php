<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'tbl_categoria';
    protected $primaryKey = 'cat_id';

    protected $fillable = [
        'eve_id',
        'cat_nome',
        'cat_descricao',
        'cat_ativo',
        'cat_aprova',
        'cat_settings',
        'cat_banner_path',
        'cat_date_start',
        'cat_date_end',
    ];

    protected $casts = [
        'cat_ativo' => 'bool',
        'cat_aprova' => 'bool',
        'cat_settings' => 'array',
        'cat_date_start' => 'datetime',
        'cat_date_end' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    // Relacionamentos
    public function event()
    {
        return $this->belongsTo(Event::class, 'eve_id', 'eve_id');
    }

    public function forms()
    {
        return $this->hasMany(Form::class, 'cat_id', 'cat_id');
    }

    public function letters()
    {
        return $this->belongsToMany(Letter::class, 'tbl_carta_categoria', 'cat_id', 'car_id');
    }

    // Acessors/Mutators
    public function getEventIdAttribute()
    {
        return $this->attributes['eve_id'] ?? null;
    }
    public function setEventIdAttribute($value): void
    {
        $this->attributes['eve_id'] = $value;
    }

    public function getNameAttribute()
    {
        return $this->attributes['cat_nome'] ?? null;
    }
    public function setNameAttribute($value): void
    {
        $this->attributes['cat_nome'] = $value;
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['cat_descricao'] ?? null;
    }
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['cat_descricao'] = $value;
    }

    public function getDateStartAttribute()
    {
        return $this->attributes['cat_date_start'] ?? null;
    }
    public function setDateStartAttribute($value): void
    {
        $this->attributes['cat_date_start'] = $value;
    }

    public function getDateEndAttribute()
    {
        return $this->attributes['cat_date_end'] ?? null;
    }
    public function setDateEndAttribute($value): void
    {
        $this->attributes['cat_date_end'] = $value;
    }

    public function getBannerPathAttribute()
    {
        return $this->attributes['cat_banner_path'] ?? null;
    }
    public function setBannerPathAttribute($value): void
    {
        $this->attributes['cat_banner_path'] = $value;
    }

    public function getActiveAttribute()
    {
        return (bool) ($this->attributes['cat_ativo'] ?? false);
    }
    public function setActiveAttribute($value): void
    {
        $this->attributes['cat_ativo'] = (bool) $value;
    }

    public function getRequiresApprovalAttribute()
    {
        return (bool) ($this->attributes['cat_aprova'] ?? false);
    }
    public function setRequiresApprovalAttribute($value): void
    {
        $this->attributes['cat_aprova'] = (bool) $value;
    }

    public function getSettingsAttribute()
    {
        return $this->attributes['cat_settings'] ?? null;
    }
    public function setSettingsAttribute($value): void
    {
        $this->attributes['cat_settings'] = $value;
    }
}
