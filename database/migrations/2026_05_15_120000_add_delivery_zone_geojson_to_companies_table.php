<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'delivery_zone_geojson')) {
                $table->json('delivery_zone_geojson')->nullable()->after('city_coverage');
            }
            if (! Schema::hasColumn('companies', 'kitchen_latitude')) {
                $table->decimal('kitchen_latitude', 10, 7)->nullable()->after('delivery_zone_geojson');
            }
            if (! Schema::hasColumn('companies', 'kitchen_longitude')) {
                $table->decimal('kitchen_longitude', 10, 7)->nullable()->after('kitchen_latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_zone_geojson',
                'kitchen_latitude',
                'kitchen_longitude',
            ]);
        });
    }
};
