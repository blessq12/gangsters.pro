<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_products', function (Blueprint $table): void {
            $table->unsignedBigInteger('old_price')
                ->nullable()
                ->after('price')
                ->comment('Старая цена в рублях (только витрина)');
        });
    }

    public function down(): void
    {
        Schema::table('PRD_products', function (Blueprint $table): void {
            $table->dropColumn('old_price');
        });
    }
};
