<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'api_service_id',
        'token_type_id',
        'value',
    ];

    // Связь с аккаунтом
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Связь с API сервисом
    public function apiService()
    {
        return $this->belongsTo(ApiService::class, 'api_service_id');
    }

    // Связь с типом токена
    public function tokenType()
    {
        return $this->belongsTo(TokenType::class, 'token_type_id');
    }
}
