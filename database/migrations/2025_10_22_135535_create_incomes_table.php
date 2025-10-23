<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('income_id')->nullable();
            $table->string('number', 191)->nullable();
            $table->date('date')->nullable();
            $table->date('last_change_date')->nullable();
            $table->string('supplier_article', 191)->nullable();
            $table->string('tech_size', 191)->nullable();
            $table->bigInteger('barcode')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->date('date_close')->nullable();
            $table->string('warehouse_name', 191)->nullable();
            $table->bigInteger('nm_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
