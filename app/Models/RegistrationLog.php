<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationLog extends Model
{
    protected $table = 'tbl_inscricao_logs';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'ins_id',
        'eve_id',
        'actor_type',
        'actor_usu_id',
        'changes',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];
}
