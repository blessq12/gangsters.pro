<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Снимок адреса доставки: lat/lng рядом с текстовыми полями.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('ORD_orders', 'delivery_lat')) {
                $table->decimal('delivery_lat', 10, 7)->nullable()->after('delivery_comment');
            }
            if (! Schema::hasColumn('ORD_orders', 'delivery_lng')) {
                $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('ORD_orders', 'delivery_lng')) {
                $table->dropColumn('delivery_lng');
            }
            if (Schema::hasColumn('ORD_orders', 'delivery_lat')) {
                $table->dropColumn('delivery_lat');
            }
        });
    }
};
