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
        {--limit=100 : Лимит записей за один запрос (по умолчанию 100)}';

    protected $description = 'Импорт данных с Wildberries API в базу данных';

    public function handle(): void
    {
        $from  = $this->option('from') ?? date('Y-m-d', strtotime('-7 days'));
        $to    = $this->option('to') ?? date('Y-m-d');
        $limit = (int)($this->option('limit') ?? 100);

        $this->info("Импорт данных с Wildberries API с $from по $to (лимит: $limit)");

        $this->importOrders($from, $to, $limit);
        $this->importSales($from, $to, $limit);
        $this->importStocks($from, $to, $limit);
        $this->importIncomes($from, $to, $limit);

        $this->info('Импорт завершён!');
    }

    protected function importOrders($from, $to, $limit)
    {
        $this->info('Загружаем заказы...');
        $created = $updated = 0;

        $response = Http::get(env('WB_API_HOST') . '/api/orders', [
            'dateFrom' => $from,
            'dateTo'   => $to,
            'key'      => env('WB_API_KEY'),
            'limit'    => $limit,
        ]);

        if ($response->failed()) {
            $this->error("Ошибка при получении заказов: " . $response->body());
            return;
        }

        $orders = $response->json('data') ?? [];

        foreach ($orders as $order) {
            $gNumber = $order['g_number'] ?? null;
            if (!$gNumber) continue;

            $record = Order::updateOrCreate(
                ['order_number' => $gNumber],
                [
                    'date'             => isset($order['date']) ? Carbon::parse($order['date'])->format('Y-m-d H:i:s') : null,
                    'last_change_date' => isset($order['last_change_date']) ? Carbon::parse($order['last_change_date'])->format('Y-m-d H:i:s') : null,
                    'supplier_article' => $order['supplier_article'] ?? null,
                    'tech_size'        => $order['tech_size'] ?? null,
                    'barcode'          => $order['barcode'] ?? null,
                    'total_price'      => $order['total_price'] ?? 0,
                    'discount_percent' => $order['discount_percent'] ?? 0,
                    'warehouse_name'   => $order['warehouse_name'] ?? null,
                    'oblast'           => $order['oblast'] ?? null,
                    'income_id'        => $order['income_id'] ?? null,
                    'odid'             => $order['odid'] ?? null,
                    'nm_id'            => $order['nm_id'] ?? null,
                    'subject'          => $order['subject'] ?? null,
                    'category'         => $order['category'] ?? null,
                    'brand'            => $order['brand'] ?? null,
                    'is_cancel'        => $order['is_cancel'] ?? false,
                    'cancel_dt'        => $order['cancel_dt'] ?? null,
                ]
            );

            $record->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->info("Заказы: создано $created, обновлено $updated");
    }

    protected function importSales($from, $to, $limit)
    {
        $this->info('Загружаем продажи...');
        $created = $updated = 0;

        $response = Http::get(env('WB_API_HOST') . '/api/sales', [
            'dateFrom' => $from,
            'dateTo'   => $to,
            'key'      => env('WB_API_KEY'),
            'limit'    => $limit,
        ]);

        if ($response->failed()) {
            $this->error("Ошибка при получении продаж: " . $response->body());
            return;
        }

        $sales = $response->json('data') ?? [];

        foreach ($sales as $sale) {
            $gNumber = $sale['g_number'] ?? null;
            if (!$gNumber) continue;

            $record = Sale::updateOrCreate(
                ['sale_number' => $gNumber],
                [
                    'date'               => isset($sale['date']) ? Carbon::parse($sale['date'])->format('Y-m-d H:i:s') : null,
                    'last_change_date'   => isset($sale['last_change_date']) ? Carbon::parse($sale['last_change_date'])->format('Y-m-d H:i:s') : null,
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
                ]
            );

            $record->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->info("Продажи: создано $created, обновлено $updated");
    }

    protected function importStocks($from, $to, $limit)
    {
        $this->info('Загружаем остатки...');
        $created = $updated = 0;

        $response = Http::get(env('WB_API_HOST') . '/api/stocks', [
            'dateFrom' => $from,
            'dateTo'   => $to,
            'key'      => env('WB_API_KEY'),
            'limit'    => $limit,
        ]);

        if ($response->failed()) {
            $this->error("Ошибка при получении остатков: " . $response->body());
            return;
        }

        $stocks = $response->json('data') ?? [];

        foreach ($stocks as $stock) {
            $nmId = $stock['nm_id'] ?? null;
            if (!$nmId) continue;

            $record = Stock::updateOrCreate(
                ['nm_id' => $nmId],
                [
                    'date'             => $stock['date'] ?? $from,
                    'last_change_date' => $stock['last_change_date'] ?? null,
                    'supplier_article' => $stock['supplier_article'] ?? null,
                    'tech_size'        => $stock['tech_size'] ?? null,
                    'barcode'          => $stock['barcode'] ?? null,
                    'quantity'         => $stock['quantity'] ?? 0,
                    'is_supply'        => $stock['is_supply'] ?? false,
                    'is_realization'   => $stock['is_realization'] ?? false,
                    'quantity_full'    => $stock['quantity_full'] ?? 0,
                    'warehouse_name'   => $stock['warehouse_name'] ?? null,
                    'in_way_to_client' => $stock['in_way_to_client'] ?? 0,
                    'in_way_from_client'=> $stock['in_way_from_client'] ?? 0,
                    'subject'          => $stock['subject'] ?? null,
                    'category'         => $stock['category'] ?? null,
                    'brand'            => $stock['brand'] ?? null,
                    'sc_code'          => $stock['sc_code'] ?? null,
                    'price'            => $stock['price'] ?? 0,
                    'discount'         => $stock['discount'] ?? 0,
                ]
            );

            $record->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->info("Остатки: создано $created, обновлено $updated");
    }

    protected function importIncomes($from, $to, $limit)
    {
        $this->info('Загружаем доходы...');
        $created = $updated = 0;

        $response = Http::get(env('WB_API_HOST') . '/api/incomes', [
            'dateFrom' => $from,
            'dateTo'   => $to,
            'key'      => env('WB_API_KEY'),
            'limit'    => $limit,
        ]);

        if ($response->failed()) {
            $this->error("Ошибка при получении доходов: " . $response->body());
            return;
        }

        $incomes = $response->json('data') ?? [];

        foreach ($incomes as $income) {
            $incomeId = $income['income_id'] ?? null;
            if (!$incomeId) continue;

            $record = Income::updateOrCreate(
                ['income_id' => $incomeId],
                [
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
                ]
            );

            $record->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->info("Доходы: создано $created, обновлено $updated");
    }
}
