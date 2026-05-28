<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->unsignedInteger('delivery_fee_kopecks')->default(0)->after('total');
            $table->json('delivery_pricing_snapshot')->nullable()->after('delivery_fee_kopecks');
        });
    }

    public function down(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee_kopecks', 'delivery_pricing_snapshot']);
        });
    }
};
