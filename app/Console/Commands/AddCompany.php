<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;

class AddCompany extends Command
{
    protected $signature = 'add:company 
                            {name : Название компании} 
                            {email? : Email компании}';

    protected $description = 'Добавляет новую компанию в базу данных';

    public function handle(): void
    {
        $name = $this->argument('name');
        $email = $this->argument('email');

        $company = Company::create([
            'name' => $name,
            'email' => $email,
        ]);

        $this->info("Компания '{$company->name}' успешно создана!");
    }
}
