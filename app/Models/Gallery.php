<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = 'tbl_galeria';
    protected $primaryKey = 'gal_id';
    public $timestamps = false;

    protected $fillable = [
        'ins_id',
        'gal_url',
        'gal_date',
        'gal_status',
        'gal_ativo',
        'gal_rotate',
        'gal_date_status',
        'gal_atualizado',
        'gal_local',
    ];

    protected $casts = [
        'gal_date' => 'datetime',
        'gal_date_status' => 'datetime',
        'gal_status' => 'integer',
        'gal_ativo' => 'integer',
        'gal_rotate' => 'integer',
    ];
}
