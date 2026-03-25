<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Кириллица в ингредиентах/описаниях должна сохраняться корректно.
        // Если БД/таблицы были созданы со старой кодировкой (latin1), получим:
        // "Incorrect string value" при вставке.

        if (Schema::hasTable('PRD_product_ingredients')) {
            DB::statement(
                "ALTER TABLE `PRD_product_ingredients` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
            );
        }

        if (Schema::hasTable('PRD_products')) {
            DB::statement(
                "ALTER TABLE `PRD_products` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
            );
        }
    }

    public function down(): void
    {
        // Обратно не откатываем: потеряем символы.
    }
};

