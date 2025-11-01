<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiService;

class AddApiService extends Command
{

    protected $signature = 'add:apiservice 
        {name : Название API сервиса} 
        {description? : Описание API сервиса} 
        {host? : URL (host) API сервиса}';

    protected $description = 'Добавляет новый API сервис (name, description, host)';

    public function handle()
    {
        $name = $this->argument('name');
        $description = $this->argument('description') ?? null;
        $host = $this->argument('host') ?? null;

        // Проверка на существующий сервис
        $existing = ApiService::where('name', $name)->first();
        if ($existing) {
            $this->warn("Сервис с именем '{$name}' уже существует (ID {$existing->id}).");
            return Command::SUCCESS;
        }

        // Создание записи
        $service = ApiService::create([
            'name' => $name,
            'description' => $description,
            'host' => $host,
        ]);

        $this->info("API сервис '{$service->name}' успешно создан!");
        $this->line("ID: {$service->id}");
        $this->line("Host: {$service->host}");

        return Command::SUCCESS;
    }
}
