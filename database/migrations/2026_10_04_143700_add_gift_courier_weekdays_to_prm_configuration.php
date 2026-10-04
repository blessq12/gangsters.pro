<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRM_configuration', function (Blueprint $table) {
            $table->json('gift_courier_weekdays')->nullable()->after('gift_courier_min_order_kopecks');
        });

        $defaultWeekdays = json_encode([1, 2, 3, 4], JSON_THROW_ON_ERROR);

        DB::table('PRM_configuration')->update([
            'gift_courier_weekdays' => $defaultWeekdays,
        ]);
    }

    public function down(): void
    {
        Schema::table('PRM_configuration', function (Blueprint $table) {
            $table->dropColumn('gift_courier_weekdays');
        });
    }
};
