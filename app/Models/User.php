<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'tbl_usuarios';
    protected $primaryKey = 'usu_id';

    protected $fillable = [

        'usu_nome',
        'usu_email',
        'usu_password',
        'usu_email_verified_at',

        // padrões Laravel
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'usu_password',
        'remember_token',
    ];

    protected $casts = [
        // cast na coluna real
        'usu_email_verified_at' => 'datetime',
    ];

    public function organizer()
    {
        return $this->hasOne(Organizer::class, 'usu_id', 'usu_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'tbl_evento_usuarios', 'usu_id', 'eve_id')
            ->withTimestamps();
    }

    // Laravel Auth precisa saber qual coluna é a senha (ok)
    public function getAuthPassword()
    {
        return $this->attributes['usu_password'] ?? null;
    }

    // Acessors/Mutators
    public function getIdAttribute()
    {
        return $this->getKey(); // permite $user->id
    }

    public function getNameAttribute()
    {
        return $this->attributes['usu_nome'] ?? null;
    }
    public function setNameAttribute($value): void
    {
        $this->attributes['usu_nome'] = $value;
    }

    public function getEmailAttribute()
    {
        return $this->attributes['usu_email'] ?? null;
    }
    public function setEmailAttribute($value): void
    {
        $this->attributes['usu_email'] = $value;
    }

    public function getEmailVerifiedAtAttribute()
    {
        return $this->attributes['usu_email_verified_at'] ?? null;
    }
    public function setEmailVerifiedAtAttribute($value): void
    {
        $this->attributes['usu_email_verified_at'] = $value;
    }

    public function getPasswordAttribute()
    {
        return $this->attributes['usu_password'] ?? null;
    }
    public function setPasswordAttribute($value): void
    {
        // se já vier hash, não re-hasha
        if (is_string($value) && (strlen($value) === 60 || str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$'))) {
            $this->attributes['usu_password'] = $value;
            return;
        }

        $this->attributes['usu_password'] = Hash::make((string) $value);
    }
}
