<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('PRD_products', 'price_amount')) {
            return;
        }

        if (!Schema::hasColumn('PRD_products', 'price')) {
            Schema::table('PRD_products', function (Blueprint $table) {
                $table->unsignedBigInteger('price')->nullable()->after('description');
            });
        }

        DB::table('PRD_products')
            ->whereNotNull('price_amount')
            ->update([
                'price' => DB::raw('ROUND(price_amount / 100)'),
            ]);

        Schema::table('PRD_products', function (Blueprint $table) {
            $table->dropColumn('price_amount');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('PRD_products', 'price')) {
            return;
        }

        Schema::table('PRD_products', function (Blueprint $table) {
            $table->unsignedBigInteger('price_amount')->nullable()->after('description');
        });

        DB::table('PRD_products')
            ->whereNotNull('price')
            ->update([
                'price_amount' => DB::raw('price * 100'),
            ]);

        Schema::table('PRD_products', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
