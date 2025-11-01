<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Account;
use App\Services\WBDataService;
use Exception;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\AddCompany::class,
        \App\Console\Commands\AddAccount::class,
        \App\Console\Commands\AddApiService::class,
        \App\Console\Commands\AddTokenType::class,
        \App\Console\Commands\AddApiToken::class,
        \App\Console\Commands\UpdateDataCommand::class,
    ];


    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            echo "=== Запуск обновления данных WB API ===" . PHP_EOL;
            $service = new WBDataService();
            $accounts = Account::with('apiToken.apiService')->get();

            foreach ($accounts as $account) {
                try {
                    echo "Обновление данных для аккаунта ID {$account->id}..." . PHP_EOL;
                    $service->updateAccountData($account);
                } catch (Exception $e) {
                    echo "Ошибка при обновлении аккаунта {$account->id}: " . $e->getMessage() . PHP_EOL;
                }
            }

            echo "=== Завершение обновления данных WB API ===" . PHP_EOL;
        })->twiceDaily(8, 20);

    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
