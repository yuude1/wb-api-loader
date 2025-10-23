<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 191)->nullable(); // g_number из API
            $table->dateTime('date')->nullable();
            $table->date('last_change_date')->nullable();
            $table->string('supplier_article', 191)->nullable();
            $table->string('tech_size', 191)->nullable();
            $table->bigInteger('barcode')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->integer('discount_percent')->default(0);
            $table->string('warehouse_name', 191)->nullable();
            $table->string('oblast', 191)->nullable();
            $table->bigInteger('income_id')->nullable();
            $table->string('odid', 191)->nullable();
            $table->bigInteger('nm_id')->nullable();
            $table->string('subject', 191)->nullable();
            $table->string('category', 191)->nullable();
            $table->string('brand', 191)->nullable();
            $table->boolean('is_cancel')->default(false);
            $table->date('cancel_dt')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
