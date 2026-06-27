<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->boolean('is_system')
                ->default(false)
                ->after('catalog_kind')
                ->comment('Системный товар: не на витрине, доступен для Order/Promotion');
        });
    }

    public function down(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
