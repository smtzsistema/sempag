<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $table = 'tbl_ficha';
    protected $primaryKey = 'fic_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Se sua PK não é auto-increment ou não é int, ajusta. Normalmente é int increment.

    protected $fillable = [

        'form_id',

        'fic_nome',
        'fic_label',
        'fic_tipo',
        'fic_obrigatorio',
        'fic_ordem',
        'fic_opcoes',
        'fic_validacoes',
        'fic_placeholder',
        'fic_help_text',
        'fic_visible_if',
        'fic_edita',
        'ficg_id',
    ];

    protected $casts = [
        // casts nas colunas reais
        'fic_obrigatorio' => 'bool',
        'fic_ordem' => 'int',
        'fic_opcoes' => 'array',
        'fic_visible_if' => 'array',
    ];

    public function getIdAttribute()
    {
        return $this->getKey(); // permite $field->id
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'form_id');
    }

    public function preset()
    {
        return $this->belongsTo(FieldPreset::class, 'ficg_id', 'ficg_id');
    }

    // Acessors/Mutators (compat)

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

    public function getRequiredAttribute()
    {
        return (bool) ($this->attributes['fic_obrigatorio'] ?? false);
    }
    public function setRequiredAttribute($value): void
    {
        $this->attributes['fic_obrigatorio'] = (bool) $value;
    }

    public function getOrderAttribute()
    {
        return (int) ($this->attributes['fic_ordem'] ?? 0);
    }
    public function setOrderAttribute($value): void
    {
        $this->attributes['fic_ordem'] = (int) $value;
    }

    public function getOptionsAttribute()
    {
        // como agora castamos fic_opcoes => array, isso já vem array/null
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

    public function getVisibleIfAttribute()
    {
        // cast fic_visible_if => array, então vem array/null
        return $this->attributes['fic_visible_if'] ?? null;
    }
    public function setVisibleIfAttribute($value): void
    {
        $this->attributes['fic_visible_if'] = $value;
    }

    public function getEditableAttribute()
    {
        return ($this->attributes['fic_edita'] ?? 'N') === 'S';
    }
    public function setEditableAttribute($value): void
    {
        $this->attributes['fic_edita'] = ((bool) $value) ? 'S' : 'N';
    }

    public function getPresetIdAttribute()
    {
        return $this->attributes['ficg_id'] ?? null;
    }
    public function setPresetIdAttribute($value): void
    {
        $this->attributes['ficg_id'] = $value;
    }
}
