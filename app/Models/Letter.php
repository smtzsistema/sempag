<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $table = 'tbl_cartas';
    protected $primaryKey = 'car_id';

    protected $fillable = [
        'eve_id',
        'car_descricao',
        'car_texto',

        // mantido por compatibilidade (1 categoria). Para múltiplas, use a relação categories().
        'cat_id',

        // e-mail
        'car_copiac',
        'car_responderto',
        'car_assunto',
        'car_tipo',
        'car_trad',
        'car_copia',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cat_id' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eve_id', 'eve_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'tbl_carta_categoria', 'car_id', 'cat_id');
    }
}
