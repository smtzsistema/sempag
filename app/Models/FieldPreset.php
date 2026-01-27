<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldPreset extends Model
{
    protected $table = 'tbl_ficha_campos';
    protected $primaryKey = 'ficg_id';

    protected $fillable = [

        'ficg_group',
        'fic_nome',
        'fic_label',
        'fic_tipo',
        'fic_opcoes',
        'fic_validacoes',
        'fic_placeholder',
        'fic_help_text',
        'fic_obrigatorio',
    ];

    protected $casts = [
        // casts nas colunas reais
        'fic_opcoes' => 'array',
        'fic_obrigatorio' => 'bool',
    ];

    public function getIdAttribute()
    {
        return $this->getKey(); // permite $preset->id
    }

    // Acessors/Mutators (compat)

    public function getGroupAttribute()
    {
        return $this->attributes['ficg_group'] ?? null;
    }

    public function setGroupAttribute($value): void
    {
        $this->attributes['ficg_group'] = $value;
    }

    public function getKeyAttribute()
    {
        return $this->attributes['fic_nome'] ?? null;
    }

    public function setKeyAttribute($value): void
    {
        $this->attributes['fic_nome'] = $value;
    }

    public function getLabelAttribute()
    {
        return $this->attributes['fic_label'] ?? null;
    }

    public function setLabelAttribute($value): void
    {
        $this->attributes['fic_label'] = $value;
    }

    public function getTypeAttribute()
    {
        return $this->attributes['fic_tipo'] ?? null;
    }

    public function setTypeAttribute($value): void
    {
        $this->attributes['fic_tipo'] = $value;
    }

    public function getOptionsAttribute()
    {
        // com cast em fic_opcoes, isso já vem array/null
        return $this->attributes['fic_opcoes'] ?? null;
    }

    public function setOptionsAttribute($value): void
    {
        $this->attributes['fic_opcoes'] = $value;
    }

    public function getValidationRulesAttribute()
    {
        return $this->attributes['fic_validacoes'] ?? null;
    }

    public function setValidationRulesAttribute($value): void
    {
        $this->attributes['fic_validacoes'] = $value;
    }


    public function getPlaceholderAttribute()
    {
        return $this->attributes['fic_placeholder'] ?? null;
    }

    public function setPlaceholderAttribute($value): void
    {
        $this->attributes['fic_placeholder'] = $value;
    }

    public function getHelpTextAttribute()
    {
        return $this->attributes['fic_help_text'] ?? null;
    }

    public function setHelpTextAttribute($value): void
    {
        $this->attributes['fic_help_text'] = $value;
    }

    public function getRequiredAttribute()
    {
        return (bool)($this->attributes['fic_obrigatorio'] ?? false);
    }

    public function setRequiredAttribute($value): void
    {
        $this->attributes['fic_obrigatorio'] = (bool)$value;
    }
}
