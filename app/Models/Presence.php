<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $table = 'tbl_presenca';
    protected $primaryKey = 'pre_id';
    public $timestamps = false;

    protected $fillable = [
        'ins_id',
        'eve_id',
        'pre_data',
        'pre_local',
        'pre_tipo',
        'pre_via',
    ];

    protected $casts = [
        'pre_data' => 'datetime',
        'pre_via' => 'integer',
        'eve_id' => 'integer',
    ];
}
