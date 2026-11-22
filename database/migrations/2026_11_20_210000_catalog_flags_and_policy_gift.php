<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Флаги товара в Catalog + gift_product_id в политике филиала.
 * Роли roll/complement/gift больше не источник правды для продажи.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('CAT_items', function (Blueprint $table): void {
            $table->boolean('is_roll')->default(false)->after('is_system');
            $table->boolean('is_complement')->default(false)->after('is_roll');
        });

        Schema::table('PRM_policies', function (Blueprint $table): void {
            $table->unsignedBigInteger('gift_product_id')->nullable()->after('branch_id');
        });

        if (Schema::hasTable('PRM_product_roles')) {
            $rollIds = DB::table('PRM_product_roles')
                ->where('role', 'roll')
                ->distinct()
                ->pluck('catalog_item_id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all();
            if ($rollIds !== []) {
                DB::table('CAT_items')->whereIn('id', $rollIds)->update(['is_roll' => true]);
            }

            $complementIds = DB::table('PRM_product_roles')
                ->where('role', 'complement')
                ->distinct()
                ->pluck('catalog_item_id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all();
            if ($complementIds !== []) {
                DB::table('CAT_items')->whereIn('id', $complementIds)->update(['is_complement' => true]);
            }

            $gifts = DB::table('PRM_product_roles')
                ->where('role', 'gift')
                ->orderBy('id')
                ->get(['branch_id', 'catalog_item_id']);

            $giftByBranch = [];
            foreach ($gifts as $row) {
                $branchId = (int) $row->branch_id;
                if ($branchId < 1 || isset($giftByBranch[$branchId])) {
                    continue;
                }
                $giftByBranch[$branchId] = (int) $row->catalog_item_id;
            }

            foreach ($giftByBranch as $branchId => $productId) {
                DB::table('PRM_policies')
                    ->where('branch_id', $branchId)
                    ->whereNull('gift_product_id')
                    ->update(['gift_product_id' => $productId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('PRM_policies', function (Blueprint $table): void {
            $table->dropColumn('gift_product_id');
        });

        Schema::table('CAT_items', function (Blueprint $table): void {
            $table->dropColumn(['is_roll', 'is_complement']);
        });
    }
};
