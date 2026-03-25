<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Удаляет колонку consist у legacy-таблицы products (не PRD_products).
     */
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }
        if (! Schema::hasColumn('products', 'consist')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('consist');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }
        if (Schema::hasColumn('products', 'consist')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('consist')->nullable();
        });
    }
};
