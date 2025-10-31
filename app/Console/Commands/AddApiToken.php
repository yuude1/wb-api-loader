<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiToken;
use App\Models\Account;
use App\Models\ApiService;
use App\Models\TokenType;

class AddApiToken extends Command
{
    protected $signature = 'add:apitoken 
                            {account_id : ID аккаунта} 
                            {api_service_id : ID API сервиса} 
                            {token_type_id : ID типа токена} 
                            {value : Значение токена}';

    protected $description = 'Добавляет API токен для аккаунта и сервиса';

    public function handle(): void
    {
        $accountId = $this->argument('account_id');
        $apiServiceId = $this->argument('api_service_id');
        $tokenTypeId = $this->argument('token_type_id');
        $value = $this->argument('value');

        // Проверка существования связанных записей
        if (!Account::find($accountId)) {
            $this->error("Аккаунт с ID {$accountId} не найден.");
            return;
        }
        if (!ApiService::find($apiServiceId)) {
            $this->error("API сервис с ID {$apiServiceId} не найден.");
            return;
        }
        if (!TokenType::find($tokenTypeId)) {
            $this->error("Тип токена с ID {$tokenTypeId} не найден.");
            return;
        }

        $token = ApiToken::create([
            'account_id' => $accountId,
            'api_service_id' => $apiServiceId,
            'token_type_id' => $tokenTypeId,
            'value' => $value,
        ]);

        $this->info("API токен успешно создан!");
    }
}
