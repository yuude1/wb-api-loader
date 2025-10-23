<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number', 191)->nullable(); // g_number из API
            $table->dateTime('date')->nullable();
            $table->date('last_change_date')->nullable();
            $table->string('supplier_article', 191)->nullable();
            $table->string('tech_size', 191)->nullable();
            $table->bigInteger('barcode')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->integer('discount_percent')->default(0);
            $table->boolean('is_supply')->default(false);
            $table->boolean('is_realization')->default(false);
            $table->decimal('promo_code_discount', 10,2)->nullable();
            $table->string('warehouse_name', 191)->nullable();
            $table->string('country_name', 191)->nullable();
            $table->string('oblast_okrug_name', 191)->nullable();
            $table->string('region_name', 191)->nullable();
            $table->bigInteger('income_id')->nullable();
            $table->string('sale_id', 191)->nullable();
            $table->string('odid', 191)->nullable();
            $table->integer('spp')->nullable();
            $table->decimal('for_pay', 10, 2)->default(0);
            $table->decimal('finished_price', 10, 2)->default(0);
            $table->decimal('price_with_disc', 10, 2)->default(0);
            $table->bigInteger('nm_id')->nullable();
            $table->string('subject', 191)->nullable();
            $table->string('category', 191)->nullable();
            $table->string('brand', 191)->nullable();
            $table->boolean('is_storno')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
