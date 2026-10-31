<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_products', function (Blueprint $table): void {
            $table->unsignedInteger('weight')
                ->nullable()
                ->after('old_price')
                ->comment('Вес порции в граммах (только product)');
        });
    }

    public function down(): void
    {
        Schema::table('PRD_products', function (Blueprint $table): void {
            $table->dropColumn('weight');
        });
    }
};
