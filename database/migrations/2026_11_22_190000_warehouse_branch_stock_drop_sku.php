<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Warehouse: убрать sku; остатки и списания — по филиалу (branch_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('WH_ingredients') && Schema::hasColumn('WH_ingredients', 'sku')) {
            Schema::table('WH_ingredients', function (Blueprint $table): void {
                $table->dropColumn('sku');
            });
        }

        $defaultBranchId = $this->defaultBranchId();

        if (Schema::hasTable('WH_stock_levels') && ! Schema::hasColumn('WH_stock_levels', 'branch_id')) {
            Schema::table('WH_stock_levels', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });

            if ($defaultBranchId !== null) {
                DB::table('WH_stock_levels')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
            } else {
                DB::table('WH_stock_levels')->delete();
            }

            try {
                Schema::table('WH_stock_levels', function (Blueprint $table): void {
                    $table->dropUnique(['ingredient_id']);
                });
            } catch (\Throwable) {
                // unique уже снят
            }

            Schema::table('WH_stock_levels', function (Blueprint $table): void {
                $table->unique(['branch_id', 'ingredient_id']);
                $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('WH_stock_writeoffs') && ! Schema::hasColumn('WH_stock_writeoffs', 'branch_id')) {
            Schema::table('WH_stock_writeoffs', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });

            if ($defaultBranchId !== null) {
                DB::table('WH_stock_writeoffs')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
            } else {
                DB::table('WH_stock_writeoffs')->delete();
            }

            Schema::table('WH_stock_writeoffs', function (Blueprint $table): void {
                $table->index('branch_id');
                $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('WH_stock_writeoffs') && Schema::hasColumn('WH_stock_writeoffs', 'branch_id')) {
            Schema::table('WH_stock_writeoffs', function (Blueprint $table): void {
                $table->dropForeign(['branch_id']);
                $table->dropIndex(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('WH_stock_levels') && Schema::hasColumn('WH_stock_levels', 'branch_id')) {
            Schema::table('WH_stock_levels', function (Blueprint $table): void {
                $table->dropForeign(['branch_id']);
                $table->dropUnique(['branch_id', 'ingredient_id']);
                $table->dropColumn('branch_id');
                $table->unique(['ingredient_id']);
            });
        }

        if (Schema::hasTable('WH_ingredients') && ! Schema::hasColumn('WH_ingredients', 'sku')) {
            Schema::table('WH_ingredients', function (Blueprint $table): void {
                $table->string('sku')->nullable()->after('name');
            });
        }
    }

    private function defaultBranchId(): ?int
    {
        if (! Schema::hasTable('CMP_branches')) {
            return null;
        }

        $id = DB::table('CMP_branches')->orderBy('id')->value('id');

        return $id !== null ? (int) $id : null;
    }
};
