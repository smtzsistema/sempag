<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $table = 'tbl_formularios';
    protected $primaryKey = 'form_id';

    protected $fillable = [

        'eve_id',
        'cat_id',
        'form_nome',
        'form_versao',
        'form_ativo',
    ];

    protected $casts = [
        // casts nas colunas reais
        'form_ativo' => 'bool',
        'form_versao' => 'int',
    ];

    public function getIdAttribute()
    {
        return $this->getKey(); // permite $form->id
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'eve_id', 'eve_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }

    public function fields()
    {
        return $this->hasMany(FormField::class, 'form_id', 'form_id')
            ->orderBy('fic_ordem');
    }

    // Acessors/Mutators (compat)
    public function getEventIdAttribute()
    {
        return $this->attributes['eve_id'] ?? null;
    }
    public function setEventIdAttribute($value): void
    {
        $this->attributes['eve_id'] = $value;
    }

    public function getCategoryIdAttribute()
    {
        return $this->attributes['cat_id'] ?? null;
    }
    public function setCategoryIdAttribute($value): void
    {
        $this->attributes['cat_id'] = $value;
    }

    public function getNameAttribute()
    {
        return $this->attributes['form_nome'] ?? null;
    }
    public function setNameAttribute($value): void
    {
        $this->attributes['form_nome'] = $value;
    }

    public function getVersionAttribute()
    {
        return $this->attributes['form_versao'] ?? null;
    }
    public function setVersionAttribute($value): void
    {
        $this->attributes['form_versao'] = $value;
    }

    public function getActiveAttribute()
    {
        return (bool) ($this->attributes['form_ativo'] ?? false);
    }
    public function setActiveAttribute($value): void
    {
        $this->attributes['form_ativo'] = (bool) $value;
    }
}
