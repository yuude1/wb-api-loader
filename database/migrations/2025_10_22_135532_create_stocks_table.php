<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->date('last_change_date')->nullable();
            $table->string('supplier_article', 191)->nullable();
            $table->string('tech_size', 191)->nullable();
            $table->bigInteger('barcode')->nullable();
            $table->integer('quantity')->default(0);
            $table->boolean('is_supply')->default(false);
            $table->boolean('is_realization')->default(false);
            $table->integer('quantity_full')->default(0);
            $table->string('warehouse_name', 191)->nullable();
            $table->integer('in_way_to_client')->default(0);
            $table->integer('in_way_from_client')->default(0);
            $table->bigInteger('nm_id')->nullable();
            $table->string('subject', 191)->nullable();
            $table->string('category', 191)->nullable();
            $table->string('brand', 191)->nullable();
            $table->bigInteger('sc_code')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
