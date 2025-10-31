<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TokenType;

class AddTokenType extends Command
{
    protected $signature = 'add:tokentype 
                            {name : Название типа токена} 
                            {description? : Описание}';

    protected $description = 'Добавляет новый тип токена';

    public function handle(): void
    {
        $name = $this->argument('name');
        $description = $this->argument('description');

        $tokenType = TokenType::create([
            'name' => $name,
            'description' => $description,
        ]);

        $this->info("Тип токена '{$tokenType->name}' успешно создан!");
    }
}
