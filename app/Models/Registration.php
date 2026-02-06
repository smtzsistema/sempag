<?php

namespace App\Models;

use App\Support\RegistrationPhoto;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $table = 'tbl_inscricao';
    protected $primaryKey = 'ins_id';


    protected $fillable = [
        // REMOVE 'ins_id',

        'eve_id', 'cat_id', 'form_id', 'usu_id',
        'ins_token', 'ins_aprovado',
        'ins_nome', 'ins_sobrenome', 'ins_nomecracha',
        'ins_email', 'ins_cpf', 'ins_cnpj',
        'ins_tel_celular', 'ins_tel_comercial',
        'ins_instituicao', 'ins_siglainstituicao',
        'ins_cargo', 'ins_cargo_cred',
        'ins_observacao',
        'ins_cep', 'ins_endereco', 'ins_numero', 'ins_complemento', 'ins_bairro', 'ins_cidade', 'ins_estado', 'ins_pais',
        'ins_dados',
        'ins_senha', 'ins_confirmacao_assunto', 'ins_confirmacao_html',
        'ins_aprovado_data', 'ins_motivo', 'ins_contesta',

        // adiciona os adicionais aqui também, se você quer salvar via mass assignment:
        'ins_adicional1', 'ins_adicional2', 'ins_adicional3', 'ins_adicional4', 'ins_adicional5',
        'ins_adicional6', 'ins_adicional7', 'ins_adicional8', 'ins_adicional9', 'ins_adicional10',
        'ins_adicional11', 'ins_adicional12', 'ins_adicional13', 'ins_adicional14', 'ins_adicional15',
        'ins_adicional16', 'ins_adicional17', 'ins_adicional18', 'ins_adicional19', 'ins_adicional20',
        'ins_adicional21', 'ins_adicional22', 'ins_adicional23', 'ins_adicional24', 'ins_adicional25',
        'ins_adicional26', 'ins_adicional27', 'ins_adicional28', 'ins_adicional29', 'ins_adicional30',
    ];

    protected $hidden = [
        'password', // accessor -> ins_senha
        'ins_senha',
    ];

    protected $casts = [
        'ins_aprovado_data' => 'datetime',
        'ins_cadastro' => 'datetime',
        'ins_dados' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function getRouteKeyName(): string
    {
        return 'ins_token';
    }

    public function getIdAttribute()
    {
        return $this->getKey(); // permite $registration->id
    }

    // -----------------
    // Relacionamentos
    // -----------------
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'form_id');
    }

    public function answers()
    {
        return $this->hasMany(RegistrationAnswer::class, 'ins_id', 'ins_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'eve_id', 'eve_id');
    }

    // -----------------
    // Acessors/Mutators (API "amigável" no código)
    // -----------------

    public function getEventIdAttribute()
    {
        return $this->attributes['eve_id'] ?? null;
    }

    public function setEventIdAttribute($value): void
    {
        $this->attributes['eve_id'] = $value;
    }

    public function getCategoryIdAttribute()
    {
        return $this->attributes['cat_id'] ?? null;
    }

    public function setCategoryIdAttribute($value): void
    {
        $this->attributes['cat_id'] = $value;
    }

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
        return $this->attributes['ins_nome'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['ins_nome'] = $value;
    }

    public function getSurnameAttribute()
    {
        return $this->attributes['ins_sobrenome'] ?? null;
    }

    public function setSurnameAttribute($value): void
    {
        $this->attributes['ins_sobrenome'] = $value;
    }

    public function getFullNameAttribute()
    {
        if (!empty($this->attributes['ins_nomecracha'])) return $this->attributes['ins_nomecracha'];

        $n = trim(($this->attributes['ins_nome'] ?? '') . ' ' . ($this->attributes['ins_sobrenome'] ?? ''));
        return $n !== '' ? $n : null;
    }

    public function setFullNameAttribute($value): void
    {
        $this->attributes['ins_nomecracha'] = $value;

        // se ainda não tiver nome/sobrenome, tenta preencher pelo full_name
        if (empty($this->attributes['ins_nome']) && is_string($value)) {
            $parts = preg_split('/\s+/', trim($value));
            if (!empty($parts)) {
                $this->attributes['ins_nome'] = array_shift($parts);
                $this->attributes['ins_sobrenome'] = !empty($parts) ? implode(' ', $parts) : null;
            }
        }
    }

    public function getEmailAttribute()
    {
        return $this->attributes['ins_email'] ?? null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['ins_email'] = $value;
    }

    public function getCpfAttribute()
    {
        return $this->attributes['ins_cpf'] ?? null;
    }

    public function setCpfAttribute($value): void
    {
        $this->attributes['ins_cpf'] = $value;
    }

    public function getCnpjAttribute()
    {
        return $this->attributes['ins_cnpj'] ?? null;
    }

    public function setCnpjAttribute($value): void
    {
        $this->attributes['ins_cnpj'] = $value;
    }

    public function getPhoneAttribute()
    {
        return $this->attributes['ins_tel_celular'] ?? null;
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['ins_tel_celular'] = $value;
    }

    public function getPhoneAltAttribute()
    {
        return $this->attributes['ins_tel_comercial'] ?? null;
    }

    public function setPhoneAltAttribute($value): void
    {
        $this->attributes['ins_tel_comercial'] = $value;
    }

    public function getCompanyAttribute()
    {
        return $this->attributes['ins_instituicao'] ?? null;
    }

    public function setCompanyAttribute($value): void
    {
        $this->attributes['ins_instituicao'] = $value;
    }

    public function getCredentialCompanyAttribute()
    {
        return $this->attributes['ins_siglainstituicao'] ?? null;
    }

    public function setCredentialCompanyAttribute($value): void
    {
        $this->attributes['ins_siglainstituicao'] = $value;
    }

    public function getRoleTitleAttribute()
    {
        return $this->attributes['ins_cargo'] ?? null;
    }

    public function setRoleTitleAttribute($value): void
    {
        $this->attributes['ins_cargo'] = $value;
    }

    public function getCredentialRoleTitleAttribute()
    {
        return $this->attributes['ins_cargo_cred'] ?? null;
    }

    public function setCredentialRoleTitleAttribute($value): void
    {
        $this->attributes['ins_cargo_cred'] = $value;
    }

    public function getNotesAttribute()
    {
        return $this->attributes['ins_observacao'] ?? null;
    }

    public function setNotesAttribute($value): void
    {
        $this->attributes['ins_observacao'] = $value;
    }

    public function getCepAttribute()
    {
        return $this->attributes['ins_cep'] ?? null;
    }

    public function setCepAttribute($value): void
    {
        $this->attributes['ins_cep'] = $value;
    }

    public function getAddressAttribute()
    {
        return $this->attributes['ins_endereco'] ?? null;
    }

    public function setAddressAttribute($value): void
    {
        $this->attributes['ins_endereco'] = $value;
    }

    public function getAddressNumberAttribute()
    {
        return $this->attributes['ins_numero'] ?? null;
    }

    public function setAddressNumberAttribute($value): void
    {
        $this->attributes['ins_numero'] = $value;
    }

    public function getAddressComplementAttribute()
    {
        return $this->attributes['ins_complemento'] ?? null;
    }

    public function setAddressComplementAttribute($value): void
    {
        $this->attributes['ins_complemento'] = $value;
    }

    public function getDistrictAttribute()
    {
        return $this->attributes['ins_bairro'] ?? null;
    }

    public function setDistrictAttribute($value): void
    {
        $this->attributes['ins_bairro'] = $value;
    }

    public function getCityAttribute()
    {
        return $this->attributes['ins_cidade'] ?? null;
    }

    public function setCityAttribute($value): void
    {
        $this->attributes['ins_cidade'] = $value;
    }

    public function getStateAttribute()
    {
        return $this->attributes['ins_estado'] ?? null;
    }

    public function setStateAttribute($value): void
    {
        $this->attributes['ins_estado'] = $value;
    }

    public function getCountryAttribute()
    {
        return $this->attributes['ins_pais'] ?? null;
    }

    public function setCountryAttribute($value): void
    {
        $this->attributes['ins_pais'] = $value;
    }

    public function getDataAttribute()
    {
        return $this->attributes['ins_dados'] ?? null;
    }

    public function setDataAttribute($value): void
    {
        $this->attributes['ins_dados'] = $value;
    }

    public function getTokenAttribute()
    {
        return $this->attributes['ins_token'] ?? null;
    }

    public function setTokenAttribute($value): void
    {
        $this->attributes['ins_token'] = $value;
    }

    public function getStatusAttribute()
    {
        return $this->attributes['ins_aprovado'] ?? null;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['ins_aprovado'] = $value;
    }

    public function getApprovedAtAttribute()
    {
        return $this->attributes['ins_aprovado_data'] ?? null;
    }

    public function setApprovedAtAttribute($value): void
    {
        $this->attributes['ins_aprovado_data'] = $value;
    }

    public function getRejectedReasonAttribute()
    {
        return $this->attributes['ins_motivo'] ?? null;
    }

    public function setRejectedReasonAttribute($value): void
    {
        $this->attributes['ins_motivo'] = $value;
    }

    public function getPasswordAttribute()
    {
        return $this->attributes['ins_senha'] ?? null;
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['ins_senha'] = $value;
    }

    public function getConfirmationSubjectAttribute()
    {
        return $this->attributes['ins_confirmacao_assunto'] ?? null;
    }

    public function setConfirmationSubjectAttribute($value): void
    {
        $this->attributes['ins_confirmacao_assunto'] = $value;
    }

    public function getConfirmationHtmlAttribute()
    {
        return $this->attributes['ins_confirmacao_html'] ?? null;
    }

    public function setConfirmationHtmlAttribute($value): void
    {
        $this->attributes['ins_confirmacao_html'] = $value;
    }

    public function __get($key)
    {
        if (preg_match('/^extra(\d{1,2})$/', $key, $m)) {
            $i = (int)$m[1];
            if ($i >= 1 && $i <= 30) {
                $col = "ins_adicional{$i}";
                return $this->attributes[$col] ?? null;
            }
        }
        return parent::__get($key);
    }

    public function __set($key, $value)
    {
        if (preg_match('/^extra(\d{1,2})$/', $key, $m)) {
            $i = (int)$m[1];
            if ($i >= 1 && $i <= 30) {
                $col = "ins_adicional{$i}";
                $this->attributes[$col] = $value;
                return;
            }
        }
        parent::__set($key, $value);
    }



    public function photos()
    {
        return $this->hasMany(Gallery::class, 'ins_id', 'ins_id');
    }

    public function activePhoto()
    {
        return $this->hasOne(Gallery::class, 'ins_id', 'ins_id')
            ->where('gal_ativo', 1)
            ->orderByDesc('gal_date');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return RegistrationPhoto::activeUrl($this);
    }

}
