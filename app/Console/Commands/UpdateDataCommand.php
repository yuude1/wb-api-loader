<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\Sale;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Income;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class UpdateDataCommand extends Command
{
    protected $signature = 'update:data';
    protected $description = 'Обновление данных из API для всех аккаунтов';

    public function handle()
    {
        $this->info('=== Запуск обновления данных WB API === ' . now());

        $accounts = Account::with(['apiToken.apiService'])->get();

        foreach ($accounts as $account) {

            $token = $account->apiToken->value ?? null;
            $apiHost = $account->apiToken->apiService->host ?? null;

            if (!$token || !$apiHost) {
                $this->warn("Пропущен аккаунт {$account->id} — отсутствует токен или URL API");
                continue;
            }

            $this->info("Обновление данных для аккаунта ID {$account->id}...");

            $endpoints = [
                'sales'   => Sale::class,
                'orders'  => Order::class,
                'stocks'  => Stock::class,
                'incomes' => Income::class,
            ];

            foreach ($endpoints as $endpoint => $model) {
                try {
                    $response = Http::retry(5, 3000, function ($exception) {
                        return $exception instanceof \Illuminate\Http\Client\RequestException &&
                               $exception->getCode() === 429;
                    })
                    ->withToken($token)
                    ->get($apiHost . '/api/v1/supplier/' . $endpoint, [
                        'dateFrom' => Carbon::now()->subDays(1)->toDateString(),
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        foreach ($data as $item) {
                            $query = ['account_id' => $account->id];

                            // уникальные ключи для каждой модели
                            if (isset($item['sale_number'])) $query['sale_number'] = $item['sale_number'];
                            if (isset($item['order_number'])) $query['order_number'] = $item['order_number'];
                            if (isset($item['nm_id'])) $query['nm_id'] = $item['nm_id'];
                            if (isset($item['income_id'])) $query['income_id'] = $item['income_id'];
                            if (isset($item['date'])) $query['date'] = $item['date'];

                            $model::updateOrCreate(
                                $query,
                                array_merge($item, ['account_id' => $account->id])
                            );
                        }

                        $this->info("Данные для {$endpoint} аккаунта {$account->id} обновлены: " . count($data) . " записей");

                    } else {
                        $this->error("Ошибка API {$endpoint} для аккаунта {$account->id}: " . $response->status());
                    }

                } catch (Exception $e) {
                    $this->error("Ошибка при обновлении {$endpoint} для аккаунта {$account->id}: " . $e->getMessage());
                    Log::error("Ошибка обновления данных", ['account' => $account->id, 'endpoint' => $endpoint, 'error' => $e->getMessage()]);
                }
            }

            $this->info("=== Обновление данных для аккаунта ID {$account->id} завершено ===");
        }

        $this->info('=== Завершение обновления данных WB API === ' . now());
    }
}
