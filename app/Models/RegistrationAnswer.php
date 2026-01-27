<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationAnswer extends Model
{
    protected $table = 'tbl_inscricao_respostas';
    protected $primaryKey = 'res_id';

    protected $fillable = [

        'res_id',
        'ins_id',
        'fic_id',
        'eve_id',
        'res_valor_texto',
        'res_valor_json',

    ];

    protected $casts = [
        // cast na coluna real
        'res_valor_json' => 'array',
    ];

    public function getIdAttribute()
    {
        return $this->getKey(); // permite $answer->id
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'ins_id', 'ins_id');
    }

    public function field()
    {
        return $this->belongsTo(FormField::class, 'fic_id', 'fic_id');
    }

    // -----------------------------
    // Acessors/Mutators (compat)
    // -----------------------------
    public function getRegistrationIdAttribute()
    {
        return $this->attributes['ins_id'] ?? null;
    }
    public function setRegistrationIdAttribute($value): void
    {
        $this->attributes['ins_id'] = $value;
    }

    public function getFieldIdAttribute()
    {
        return $this->attributes['fic_id'] ?? null;
    }
    public function setFieldIdAttribute($value): void
    {
        $this->attributes['fic_id'] = $value;
    }

    public function getEventIdAttribute()
    {
        return $this->attributes['eve_id'] ?? null;
    }
    public function setEventIdAttribute($value): void
    {
        $this->attributes['eve_id'] = $value;
    }

    public function getValueTextAttribute()
    {
        return $this->attributes['res_valor_texto'] ?? null;
    }
    public function setValueTextAttribute($value): void
    {
        $this->attributes['res_valor_texto'] = $value;
    }

    public function getValueJsonAttribute()
    {
        // com cast em res_valor_json, isso já vem array/null
        return $this->attributes['res_valor_json'] ?? null;
    }
    public function setValueJsonAttribute($value): void
    {
        $this->attributes['res_valor_json'] = $value;
    }
}
