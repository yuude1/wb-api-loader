<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Income;

class ImportWbData extends Command
{
    protected $signature = 'import:wbdata 
        {--from= : Дата начала YYYY-MM-DD} 
        {--to= : Дата конца YYYY-MM-DD} 
        {--limit=500 : Лимит записей за один запрос (по умолчанию 500)}';

    protected $description = 'Импорт данных с Wildberries API в базу данных';

    public function handle(): void
    {
        $from  = $this->option('from') ?? date('Y-m-d', strtotime('-7 days'));
        $to    = $this->option('to') ?? date('Y-m-d');
        $limit = (int)($this->option('limit') ?? 500);

        $this->info("Импорт данных с Wildberries API с $from по $to (лимит: $limit)");

        $this->importOrders($from, $to, $limit);
        $this->importSales($from, $to, $limit);
        $this->importStocks($from, $to, $limit);
        $this->importIncomes($from, $to, $limit);

        $this->info('Импорт завершён.');
    }

    protected function fetchAllPages($endpoint, $from, $to, $limit)
    {
        $allData = [];
        $page = 1;

        do {
            $response = Http::get(env('WB_API_HOST') . "/api/{$endpoint}", [
                'dateFrom' => $from,
                'dateTo'   => $to,
                'key'      => env('WB_API_KEY'),
                'limit'    => $limit,
                'page'     => $page,
            ]);

            if ($response->failed()) {
                $this->error("Ошибка при получении {$endpoint}: " . $response->body());
                break;
            }

            $data = $response->json('data') ?? [];

            if (empty($data)) break;

            $allData = array_merge($allData, $data);
            $page++;

        } while (count($data) === $limit);

        return $allData;
    }

    protected function importOrders($from, $to, $limit)
    {
        $this->info('Загружаем заказы...');
        $orders = $this->fetchAllPages('orders', $from, $to, $limit);

        if (empty($orders)) {
            $this->info('Нет данных для заказов.');
            return;
        }

        $existing = Order::pluck('order_number')->toArray();
        $mapped = [];

        foreach ($orders as $order) {
            if (empty($order['g_number'])) continue;

            $mapped[] = [
                'order_number'      => $order['g_number'],
                'date'              => isset($order['date']) ? Carbon::parse($order['date']) : null,
                'last_change_date'  => isset($order['last_change_date']) ? Carbon::parse($order['last_change_date']) : null,
                'supplier_article'  => $order['supplier_article'] ?? null,
                'tech_size'         => $order['tech_size'] ?? null,
                'barcode'           => $order['barcode'] ?? null,
                'total_price'       => $order['total_price'] ?? 0,
                'discount_percent'  => $order['discount_percent'] ?? 0,
                'warehouse_name'    => $order['warehouse_name'] ?? null,
                'oblast'            => $order['oblast'] ?? null,
                'income_id'         => $order['income_id'] ?? null,
                'odid'              => $order['odid'] ?? null,
                'nm_id'             => $order['nm_id'] ?? null,
                'subject'           => $order['subject'] ?? null,
                'category'          => $order['category'] ?? null,
                'brand'             => $order['brand'] ?? null,
                'is_cancel'         => $order['is_cancel'] ?? false,
                'cancel_dt'         => $order['cancel_dt'] ?? null,
            ];
        }

        $newRecords = array_filter($mapped, fn($item) => !in_array($item['order_number'], $existing));
        $updatedRecords = array_filter($mapped, fn($item) => in_array($item['order_number'], $existing));

        if (!empty($mapped)) {
            Order::upsert($mapped, ['order_number']);
        }

        $this->info("Заказы: добавлено " . count($newRecords) . ", обновлено " . count($updatedRecords));
    }

    protected function importSales($from, $to, $limit)
    {
        $this->info('Загружаем продажи...');
        $sales = $this->fetchAllPages('sales', $from, $to, $limit);

        if (empty($sales)) {
            $this->info('Нет данных для продаж.');
            return;
        }

        $existing = Sale::pluck('sale_number')->toArray();
        $mapped = [];

        foreach ($sales as $sale) {
            if (empty($sale['g_number'])) continue;

            $mapped[] = [
                'sale_number'        => $sale['g_number'],
                'date'               => isset($sale['date']) ? Carbon::parse($sale['date']) : null,
                'last_change_date'   => isset($sale['last_change_date']) ? Carbon::parse($sale['last_change_date']) : null,
                'supplier_article'   => $sale['supplier_article'] ?? null,
                'tech_size'          => $sale['tech_size'] ?? null,
                'barcode'            => $sale['barcode'] ?? null,
                'total_price'        => $sale['total_price'] ?? 0,
                'discount_percent'   => $sale['discount_percent'] ?? 0,
                'is_supply'          => $sale['is_supply'] ?? false,
                'is_realization'     => $sale['is_realization'] ?? false,
                'promo_code_discount'=> $sale['promo_code_discount'] ?? null,
                'warehouse_name'     => $sale['warehouse_name'] ?? null,
                'country_name'       => $sale['country_name'] ?? null,
                'oblast_okrug_name'  => $sale['oblast_okrug_name'] ?? null,
                'region_name'        => $sale['region_name'] ?? null,
                'income_id'          => $sale['income_id'] ?? null,
                'sale_id'            => $sale['sale_id'] ?? null,
                'odid'               => $sale['odid'] ?? null,
                'spp'                => $sale['spp'] ?? null,
                'for_pay'            => $sale['for_pay'] ?? 0,
                'finished_price'     => $sale['finished_price'] ?? 0,
                'price_with_disc'    => $sale['price_with_disc'] ?? 0,
                'nm_id'              => $sale['nm_id'] ?? null,
                'subject'            => $sale['subject'] ?? null,
                'category'           => $sale['category'] ?? null,
                'brand'              => $sale['brand'] ?? null,
                'is_storno'          => $sale['is_storno'] ?? null,
            ];
        }

        $newRecords = array_filter($mapped, fn($item) => !in_array($item['sale_number'], $existing));
        $updatedRecords = array_filter($mapped, fn($item) => in_array($item['sale_number'], $existing));

        if (!empty($mapped)) {
            Sale::upsert($mapped, ['sale_number']);
        }

        $this->info("Продажи: добавлено " . count($newRecords) . ", обновлено " . count($updatedRecords));
    }

    protected function importStocks($from, $to, $limit)
    {
        $this->info('Загружаем остатки...');
        $stocks = $this->fetchAllPages('stocks', $from, $to, $limit);

        if (empty($stocks)) {
            $this->info('Нет данных для остатков.');
            return;
        }

        $existing = Stock::pluck('nm_id')->toArray();
        $mapped = [];

        foreach ($stocks as $stock) {
            if (empty($stock['nm_id'])) continue;

            $mapped[] = [
                'nm_id'              => $stock['nm_id'],
                'date'               => $stock['date'] ?? $from,
                'last_change_date'   => $stock['last_change_date'] ?? null,
                'supplier_article'   => $stock['supplier_article'] ?? null,
                'tech_size'          => $stock['tech_size'] ?? null,
                'barcode'            => $stock['barcode'] ?? null,
                'quantity'           => $stock['quantity'] ?? 0,
                'is_supply'          => $stock['is_supply'] ?? false,
                'is_realization'     => $stock['is_realization'] ?? false,
                'quantity_full'      => $stock['quantity_full'] ?? 0,
                'warehouse_name'     => $stock['warehouse_name'] ?? null,
                'in_way_to_client'   => $stock['in_way_to_client'] ?? 0,
                'in_way_from_client' => $stock['in_way_from_client'] ?? 0,
                'subject'            => $stock['subject'] ?? null,
                'category'           => $stock['category'] ?? null,
                'brand'              => $stock['brand'] ?? null,
                'sc_code'            => $stock['sc_code'] ?? null,
                'price'              => $stock['price'] ?? 0,
                'discount'           => $stock['discount'] ?? 0,
            ];
        }

        $newRecords = array_filter($mapped, fn($item) => !in_array($item['nm_id'], $existing));
        $updatedRecords = array_filter($mapped, fn($item) => in_array($item['nm_id'], $existing));

        if (!empty($mapped)) {
            Stock::upsert($mapped, ['nm_id']);
        }

        $this->info("Остатки: добавлено " . count($newRecords) . ", обновлено " . count($updatedRecords));
    }

    protected function importIncomes($from, $to, $limit)
    {
        $this->info('Загружаем доходы...');
        $incomes = $this->fetchAllPages('incomes', $from, $to, $limit);

        if (empty($incomes)) {
            $this->info('Нет данных для доходов.');
            return;
        }

        $existing = Income::pluck('income_id')->toArray();
        $mapped = [];

        foreach ($incomes as $income) {
            if (empty($income['income_id'])) continue;

            $mapped[] = [
                'income_id'        => $income['income_id'],
                'number'           => $income['number'] ?? null,
                'date'             => $income['date'] ?? null,
                'last_change_date' => $income['last_change_date'] ?? null,
                'supplier_article' => $income['supplier_article'] ?? null,
                'tech_size'        => $income['tech_size'] ?? null,
                'barcode'          => $income['barcode'] ?? null,
                'quantity'         => $income['quantity'] ?? 0,
                'total_price'      => $income['total_price'] ?? 0,
                'date_close'       => $income['date_close'] ?? null,
                'warehouse_name'   => $income['warehouse_name'] ?? null,
                'nm_id'            => $income['nm_id'] ?? null,
            ];
        }

        $newRecords = array_filter($mapped, fn($item) => !in_array($item['income_id'], $existing));
        $updatedRecords = array_filter($mapped, fn($item) => in_array($item['income_id'], $existing));

        if (!empty($mapped)) {
            Income::upsert($mapped, ['income_id']);
        }

        $this->info("Доходы: добавлено " . count($newRecords) . ", обновлено " . count($updatedRecords));
    }
}
