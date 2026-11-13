<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ORD_orders')) {
            return;
        }

        Schema::table('ORD_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('ORD_orders', 'frontpad_order_id')) {
                $table->dropColumn('frontpad_order_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ORD_orders')) {
            return;
        }

        Schema::table('ORD_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('ORD_orders', 'frontpad_order_id')) {
                $table->string('frontpad_order_id', 64)->nullable()->after('external_order_id');
            }
        });
    }
};
