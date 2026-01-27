<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    protected $table = 'tbl_organizadoras';
    protected $primaryKey = 'org_id';

    protected $fillable = [

        'usu_id',
        'org_nome',
    ];

    public function getIdAttribute()
    {
        return $this->getKey(); // permite $organizer->id
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usu_id', 'usu_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'org_id', 'org_id');
    }

    // compat
    public function getUserIdAttribute()
    {
        return $this->attributes['usu_id'] ?? null;
    }
    public function setUserIdAttribute($value): void
    {
        $this->attributes['usu_id'] = $value;
    }

    public function getNameAttribute()
    {
        return $this->attributes['org_nome'] ?? null;
    }
    public function setNameAttribute($value): void
    {
        $this->attributes['org_nome'] = $value;
    }
}
