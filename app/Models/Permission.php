<?php

namespace App\Models;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $fillable = [
        'name',
        'guard_name',
        'perm_label',
        'perm_group',
        'perm_desc',
    ];
}
