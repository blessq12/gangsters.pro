<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->string('source', 32)->default('site')->after('id');
            $table->string('partner_code', 64)->nullable()->after('checkout_id');
            $table->string('external_order_id', 128)->nullable()->after('partner_code');
        });

        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->uuid('checkout_id')->nullable()->change();
        });

        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->dropUnique(['checkout_id']);
            $table->unique('checkout_id');
            $table->unique(['partner_code', 'external_order_id'], 'ord_orders_partner_external_unique');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->dropUnique('ord_orders_partner_external_unique');
            $table->dropUnique(['checkout_id']);
            $table->dropIndex(['source']);
        });

        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->dropColumn(['source', 'partner_code', 'external_order_id']);
            $table->uuid('checkout_id')->nullable(false)->change();
            $table->unique('checkout_id');
        });
    }
};
