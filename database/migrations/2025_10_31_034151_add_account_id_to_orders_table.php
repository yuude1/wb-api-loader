<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // создаём nullable foreignId (unsignedBigInt) и внешний ключ на accounts.id
            $table->foreignId('account_id')
                  ->after('id')      // необязательно, просто порядок колонок
                  ->nullable()
                  ->constrained('accounts') // явно указываем таблицу
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // сначала удаляем foreign key, потом колонку
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
