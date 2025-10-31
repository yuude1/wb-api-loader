<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiService;

class AddApiService extends Command
{
    protected $signature = 'add:apiservice 
                            {name : Название API сервиса} 
                            {description? : Описание}';

    protected $description = 'Добавляет новый API сервис';

    public function handle(): void
    {
        $name = $this->argument('name');
        $description = $this->argument('description');

        $service = ApiService::create([
            'name' => $name,
            'description' => $description,
        ]);

        $this->info("API сервис '{$service->name}' успешно создан!");
    }
}
