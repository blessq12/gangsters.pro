<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Кандидаты на подарок — флаг позиции меню филиала.
 * gift_product_id в PRM больше не источник правды.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('CMP_branch_menu_items', 'is_gift_candidate')) {
            Schema::table('CMP_branch_menu_items', function (Blueprint $table): void {
                $table->boolean('is_gift_candidate')->default(false)->after('is_available');
            });
        }

        if (Schema::hasColumn('PRM_policies', 'gift_product_id')) {
            $policies = DB::table('PRM_policies')
                ->whereNotNull('gift_product_id')
                ->get(['branch_id', 'gift_product_id']);

            foreach ($policies as $row) {
                $branchId = (int) $row->branch_id;
                $productId = (int) $row->gift_product_id;
                if ($branchId < 1 || $productId < 1) {
                    continue;
                }

                DB::table('CMP_branch_menu_items')
                    ->where('branch_id', $branchId)
                    ->where('catalog_item_id', $productId)
                    ->update(['is_gift_candidate' => true]);
            }

            Schema::table('PRM_policies', function (Blueprint $table): void {
                $table->dropColumn('gift_product_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('PRM_policies', 'gift_product_id')) {
            Schema::table('PRM_policies', function (Blueprint $table): void {
                $table->unsignedBigInteger('gift_product_id')->nullable()->after('branch_id');
            });
        }

        if (Schema::hasColumn('CMP_branch_menu_items', 'is_gift_candidate')) {
            $gifts = DB::table('CMP_branch_menu_items')
                ->where('is_gift_candidate', true)
                ->orderBy('id')
                ->get(['branch_id', 'catalog_item_id']);

            $seen = [];
            foreach ($gifts as $row) {
                $branchId = (int) $row->branch_id;
                if ($branchId < 1 || isset($seen[$branchId])) {
                    continue;
                }
                $seen[$branchId] = true;
                DB::table('PRM_policies')
                    ->where('branch_id', $branchId)
                    ->whereNull('gift_product_id')
                    ->update(['gift_product_id' => (int) $row->catalog_item_id]);
            }

            Schema::table('CMP_branch_menu_items', function (Blueprint $table): void {
                $table->dropColumn('is_gift_candidate');
            });
        }
    }
};
