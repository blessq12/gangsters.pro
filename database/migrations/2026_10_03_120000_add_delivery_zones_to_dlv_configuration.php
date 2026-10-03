<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('DLV_configuration', function (Blueprint $table) {
            $table->json('delivery_zones')->nullable()->after('delivery_zone_geojson');
        });

        $rows = DB::table('DLV_configuration')->select([
            'id',
            'delivery_fee_kopecks',
            'delivery_zone_geojson',
            'delivery_zones',
        ])->get();

        foreach ($rows as $row) {
            if ($row->delivery_zones !== null && $row->delivery_zones !== '') {
                continue;
            }

            $geometry = $this->decodeJson($row->delivery_zone_geojson);
            if (! is_array($geometry)) {
                continue;
            }

            $type = $geometry['type'] ?? null;
            if (! is_string($type) || ! in_array($type, ['Polygon', 'MultiPolygon'], true)) {
                continue;
            }

            $zones = [[
                'id' => (string) Str::uuid(),
                'name' => 'Основная',
                'delivery_fee_kopecks' => max(0, (int) ($row->delivery_fee_kopecks ?? 0)),
                'is_remote' => false,
                'geometry' => $geometry,
            ]];

            DB::table('DLV_configuration')
                ->where('id', $row->id)
                ->update(['delivery_zones' => json_encode($zones, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        Schema::table('DLV_configuration', function (Blueprint $table) {
            $table->dropColumn('delivery_zones');
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
};
