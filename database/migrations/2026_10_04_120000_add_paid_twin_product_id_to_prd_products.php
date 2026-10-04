<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_products', function (Blueprint $table): void {
            $table->unsignedBigInteger('paid_twin_product_id')
                ->nullable()
                ->after('meta_is_complement_set');

            $table->foreign('paid_twin_product_id')
                ->references('id')
                ->on('PRD_products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('PRD_products', function (Blueprint $table): void {
            $table->dropForeign(['paid_twin_product_id']);
            $table->dropColumn('paid_twin_product_id');
        });
    }
};
