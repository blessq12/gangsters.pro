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
            if (! Schema::hasColumn('ORD_orders', 'payment_method')) {
                $table->string('payment_method', 32)->nullable()->after('payment_id');
            }
            if (! Schema::hasColumn('ORD_orders', 'change_from_rubles')) {
                $table->integer('change_from_rubles')->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ORD_orders')) {
            return;
        }

        Schema::table('ORD_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('ORD_orders', 'change_from_rubles')) {
                $table->dropColumn('change_from_rubles');
            }
            if (Schema::hasColumn('ORD_orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
