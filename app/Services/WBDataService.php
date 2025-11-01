<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Sale;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Income;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Exception;

class WBDataService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30]);
    }

    /**
     * Обновление данных для одного аккаунта.
     */
    public function updateAccountData(Account $account): void
    {
        $token = $account->apiToken->value ?? null;
        $apiHost = $account->apiToken->apiService->host ?? null;

        if (!$token || !$apiHost) {
            throw new Exception("Отсутствует токен или URL API для аккаунта {$account->id}");
        }

        $headers = [
            'Authorization' => $token,
            'Accept' => 'application/json',
        ];

        $endpoints = [
            'sales'   => Sale::class,
            'orders'  => Order::class,
            'stocks'  => Stock::class,
            'incomes' => Income::class,
        ];

        foreach ($endpoints as $endpoint => $model) {
            $this->fetchEndpoint($apiHost.'/'.$endpoint, $model, $account, $headers);
        }
    }

    protected function fetchEndpoint(string $url, string $model, Account $account, array $headers): void
    {
        $attempts = 0;
        $maxAttempts = 5;
        $delaySeconds = 5;

        do {
            $attempts++;
            try {
                // Запрос к API
                $response = $this->http->get($url, ['headers' => $headers]);
                $data = json_decode($response->getBody(), true);

                foreach ($data as $item) {
                    // Проверка на существующую запись по date
                    if (isset($item['date'])) {
                        $existing = $model::where('account_id', $account->id)
                                          ->where('date', $item['date'])
                                          ->first();
                        if ($existing) {
                            continue; // запись уже есть — пропускаем
                        }
                    }

                    $query = ['account_id' => $account->id];

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

                echo "[".ucfirst($endpoint)."] Данные для аккаунта {$account->id} обновлены: ".count($data)." записей".PHP_EOL;
                break;

            } catch (RequestException $e) {
                if ($e->getCode() == 429) {
                    echo "Слишком много запросов. Повтор через {$delaySeconds} сек...".PHP_EOL;
                    sleep($delaySeconds);
                } else {
                    Log::error("Ошибка при запросе {$url} для аккаунта {$account->id}: ".$e->getMessage());
                    echo "Ошибка при запросе {$url} для аккаунта {$account->id}: ".$e->getMessage().PHP_EOL;
                    break;
                }
            } catch (Exception $e) {
                Log::error("Ошибка обработки данных для аккаунта {$account->id}: ".$e->getMessage());
                echo "Ошибка обработки данных для аккаунта {$account->id}: ".$e->getMessage().PHP_EOL;
                break;
            }
        } while ($attempts < $maxAttempts);
    }
}
