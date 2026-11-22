<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Порог бесплатной доставки — коммерческое правило Promotion, не логистика Delivery.
 * Откат переноса из DLV обратно в PRM.delivery_benefit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('DLV_configurations', 'free_delivery_threshold_kopecks')) {
            return;
        }

        $configs = DB::table('DLV_configurations')
            ->select(['branch_id', 'free_delivery_threshold_kopecks'])
            ->get();

        foreach ($configs as $config) {
            $branchId = (int) $config->branch_id;
            if ($branchId < 1) {
                continue;
            }

            $threshold = $config->free_delivery_threshold_kopecks;
            $active = $threshold !== null;
            $kopecks = $active ? max(0, (int) $threshold) : 0;

            DB::table('PRM_policies')
                ->where('branch_id', $branchId)
                ->update([
                    'delivery_benefit' => json_encode([
                        'free_delivery_threshold_kopecks' => $kopecks,
                        'outside_zone_surcharge_kopecks' => 0,
                        'below_threshold_fee_mode' => 'base_tariff',
                        'in_zone_at_threshold_fee_mode' => 'free',
                        'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
                        'is_active' => $active,
                    ], JSON_THROW_ON_ERROR),
                ]);
        }

        Schema::table('DLV_configurations', function (Blueprint $table): void {
            $table->dropColumn('free_delivery_threshold_kopecks');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('DLV_configurations', 'free_delivery_threshold_kopecks')) {
            Schema::table('DLV_configurations', function (Blueprint $table): void {
                $table->unsignedBigInteger('free_delivery_threshold_kopecks')->nullable()->after('min_order_amount_kopecks');
            });
        }

        $policies = DB::table('PRM_policies')->select(['branch_id', 'delivery_benefit'])->get();
        foreach ($policies as $policy) {
            $branchId = (int) $policy->branch_id;
            if ($branchId < 1) {
                continue;
            }

            $raw = $policy->delivery_benefit;
            $benefit = is_string($raw) ? json_decode($raw, true) : $raw;
            if (! is_array($benefit)) {
                continue;
            }

            $active = (bool) ($benefit['is_active'] ?? false);
            $threshold = isset($benefit['free_delivery_threshold_kopecks'])
                ? (int) $benefit['free_delivery_threshold_kopecks']
                : null;

            DB::table('DLV_configurations')
                ->where('branch_id', $branchId)
                ->update([
                    'free_delivery_threshold_kopecks' => $active && $threshold !== null && $threshold >= 0
                        ? $threshold
                        : null,
                ]);
        }
    }
};
