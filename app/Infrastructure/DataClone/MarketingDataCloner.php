<?php

namespace App\Infrastructure\DataClone;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * Клонирование MKT_banners / MKT_promotions.
 */
final class MarketingDataCloner
{
    /**
     * @return array{banners: int, promotions: int}
     */
    public function clone(Connection $source): array
    {
        $banners = $source->table('MKT_banners')->orderBy('id')->get();
        $promotions = $source->table('MKT_promotions')->orderBy('id')->get();

        DB::transaction(function () use ($banners, $promotions): void {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('MKT_banners')->delete();
            DB::table('MKT_promotions')->delete();

            foreach ($banners as $row) {
                DB::table('MKT_banners')->insert([
                    'id' => (int) $row->id,
                    'image_desktop' => $row->image_desktop,
                    'image_mobile' => $row->image_mobile,
                    'sort_order' => (int) $row->sort_order,
                    'is_active' => (bool) $row->is_active,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            foreach ($promotions as $row) {
                DB::table('MKT_promotions')->insert([
                    'id' => (int) $row->id,
                    'title' => (string) $row->title,
                    'body' => $row->body,
                    'image' => $row->image,
                    'sort_order' => (int) $row->sort_order,
                    'is_active' => (bool) $row->is_active,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return [
            'banners' => $banners->count(),
            'promotions' => $promotions->count(),
        ];
    }
}
