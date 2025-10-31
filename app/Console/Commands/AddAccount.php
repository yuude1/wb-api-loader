<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\Company;

class AddAccount extends Command
{
    protected $signature = 'add:account 
                            {company_id : ID компании} 
                            {name : Название аккаунта} 
                            {email? : Email аккаунта}';

    protected $description = 'Добавляет новый аккаунт компании';

    public function handle(): void
    {
        $companyId = $this->argument('company_id');
        $name = $this->argument('name');
        $email = $this->argument('email');

        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Компания с ID {$companyId} не найдена.");
            return;
        }

        $account = Account::create([
            'company_id' => $companyId,
            'name' => $name,
            'email' => $email,
        ]);

        $this->info("Аккаунт '{$account->name}' успешно создан!");
    }
}
