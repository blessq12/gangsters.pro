<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Жёстко привязывает ORD_orders.client_id к CRM_clients
 * (nullOnDelete — заказ остаётся, ссылка сбрасывается).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ORD_orders') || ! Schema::hasTable('CRM_clients')) {
            return;
        }

        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->foreign('client_id')
                ->references('id')
                ->on('CRM_clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ORD_orders')) {
            return;
        }

        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
    }
};
