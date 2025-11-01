<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'email',
    ];

    // Связь с токеном (один токен на аккаунт)
    public function apiToken()
    {
        return $this->hasOne(ApiToken::class, 'account_id'); // внешний ключ account_id
    }

    // Связь с компанией
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
