<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Per-branch: меню филиала, политики, доставка, маршрутные листы.
 * Синглтоны PRM/DLV убираются — данные копируются на каждый активный филиал.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('CMP_branch_menu_items')) {
            Schema::create('CMP_branch_menu_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('catalog_item_id');
                $table->boolean('is_available')->default(true);
                $table->unsignedInteger('price_kopecks');
                $table->timestamps();

                $table->unique(['branch_id', 'catalog_item_id']);
                $table->index('branch_id');
                $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('PRM_policies') && ! Schema::hasColumn('PRM_policies', 'branch_id')) {
            Schema::table('PRM_policies', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });
        }

        if (Schema::hasTable('PRM_product_roles') && ! Schema::hasColumn('PRM_product_roles', 'branch_id')) {
            Schema::table('PRM_product_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });
        }

        if (Schema::hasTable('PRM_zones') && ! Schema::hasColumn('PRM_zones', 'branch_id')) {
            Schema::table('PRM_zones', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });
            // PK был строковый id зоны; для per-branch нужен составной ключ.
            Schema::table('PRM_zones', function (Blueprint $table): void {
                $table->dropPrimary(['id']);
            });
            Schema::table('PRM_zones', function (Blueprint $table): void {
                $table->unique(['branch_id', 'id'], 'prm_zones_branch_id_uq');
                $table->index('branch_id');
            });
        }

        if (Schema::hasTable('DLV_route_sheets') && ! Schema::hasColumn('DLV_route_sheets', 'branch_id')) {
            Schema::table('DLV_route_sheets', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->index('branch_id');
            });
        }

        $this->backfillPerBranch();

        if (Schema::hasTable('PRM_policies') && Schema::hasColumn('PRM_policies', 'branch_id')) {
            $this->makeBranchNotNull('PRM_policies', 'branch_id');
            Schema::table('PRM_policies', function (Blueprint $table): void {
                $table->unique('branch_id');
            });
        }

        if (Schema::hasTable('PRM_product_roles') && Schema::hasColumn('PRM_product_roles', 'branch_id')) {
            $this->makeBranchNotNull('PRM_product_roles', 'branch_id');
            try {
                Schema::table('PRM_product_roles', function (Blueprint $table): void {
                    $table->dropUnique(['catalog_item_id', 'role']);
                });
            } catch (\Throwable) {
                // ignore if name differs
            }
            Schema::table('PRM_product_roles', function (Blueprint $table): void {
                $table->unique(['branch_id', 'catalog_item_id', 'role'], 'prm_roles_branch_item_role_uq');
            });
        }

        if (Schema::hasTable('DLV_configurations')) {
            // Уникальность branch_id (одна конфигурация на филиал).
            $dupes = DB::table('DLV_configurations')
                ->select('branch_id', DB::raw('COUNT(*) as c'))
                ->whereNotNull('branch_id')
                ->groupBy('branch_id')
                ->having('c', '>', 1)
                ->exists();
            if (! $dupes) {
                Schema::table('DLV_configurations', function (Blueprint $table): void {
                    $table->unique('branch_id');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('CMP_branch_menu_items');

        if (Schema::hasTable('PRM_policies') && Schema::hasColumn('PRM_policies', 'branch_id')) {
            Schema::table('PRM_policies', function (Blueprint $table): void {
                $table->dropUnique(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('PRM_product_roles') && Schema::hasColumn('PRM_product_roles', 'branch_id')) {
            Schema::table('PRM_product_roles', function (Blueprint $table): void {
                $table->dropUnique('prm_roles_branch_item_role_uq');
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('PRM_zones') && Schema::hasColumn('PRM_zones', 'branch_id')) {
            Schema::table('PRM_zones', function (Blueprint $table): void {
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('DLV_route_sheets') && Schema::hasColumn('DLV_route_sheets', 'branch_id')) {
            Schema::table('DLV_route_sheets', function (Blueprint $table): void {
                $table->dropColumn('branch_id');
            });
        }
    }

    private function backfillPerBranch(): void
    {
        if (! Schema::hasTable('CMP_branches')) {
            return;
        }

        $branchIds = DB::table('CMP_branches')->where('is_active', true)->pluck('id')->map(static fn ($id) => (int) $id)->all();
        if ($branchIds === []) {
            $branchIds = DB::table('CMP_branches')->pluck('id')->map(static fn ($id) => (int) $id)->all();
        }
        if ($branchIds === []) {
            return;
        }

        $primaryBranchId = $branchIds[0];

        // Меню: active catalog items × branches с regular price.
        if (Schema::hasTable('CAT_items') && Schema::hasTable('CAT_prices')) {
            $items = DB::table('CAT_items')->where('status', 'active')->pluck('id');
            $prices = DB::table('CAT_prices')
                ->where('type', 'regular')
                ->pluck('amount_kopecks', 'item_id');

            foreach ($branchIds as $branchId) {
                foreach ($items as $itemId) {
                    $itemId = (int) $itemId;
                    $exists = DB::table('CMP_branch_menu_items')
                        ->where('branch_id', $branchId)
                        ->where('catalog_item_id', $itemId)
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    DB::table('CMP_branch_menu_items')->insert([
                        'branch_id' => $branchId,
                        'catalog_item_id' => $itemId,
                        'is_available' => true,
                        'price_kopecks' => (int) ($prices[$itemId] ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // PRM_policies: singleton → per branch.
        if (Schema::hasTable('PRM_policies') && Schema::hasColumn('PRM_policies', 'branch_id')) {
            $source = DB::table('PRM_policies')->orderBy('id')->first();
            if ($source !== null) {
                DB::table('PRM_policies')->where('id', $source->id)->update(['branch_id' => $primaryBranchId]);
                foreach ($branchIds as $branchId) {
                    if ($branchId === $primaryBranchId) {
                        continue;
                    }
                    if (DB::table('PRM_policies')->where('branch_id', $branchId)->exists()) {
                        continue;
                    }
                    DB::table('PRM_policies')->insert([
                        'branch_id' => $branchId,
                        'gift_rules' => $source->gift_rules,
                        'delivery_benefit' => $source->delivery_benefit,
                        'complement_set_benefit' => $source->complement_set_benefit,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // PRM_product_roles.
        if (Schema::hasTable('PRM_product_roles') && Schema::hasColumn('PRM_product_roles', 'branch_id')) {
            $roles = DB::table('PRM_product_roles')->whereNull('branch_id')->get();
            foreach ($roles as $role) {
                DB::table('PRM_product_roles')->where('id', $role->id)->update(['branch_id' => $primaryBranchId]);
                foreach ($branchIds as $branchId) {
                    if ($branchId === $primaryBranchId) {
                        continue;
                    }
                    $exists = DB::table('PRM_product_roles')
                        ->where('branch_id', $branchId)
                        ->where('catalog_item_id', $role->catalog_item_id)
                        ->where('role', $role->role)
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    DB::table('PRM_product_roles')->insert([
                        'branch_id' => $branchId,
                        'catalog_item_id' => $role->catalog_item_id,
                        'role' => $role->role,
                        'name_snapshot' => $role->name_snapshot,
                        'price_kopecks_snapshot' => $role->price_kopecks_snapshot,
                        'paid_twin_catalog_item_id' => $role->paid_twin_catalog_item_id ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // DLV_configurations: привязать singleton к primary, клонировать на остальные.
        if (Schema::hasTable('DLV_configurations')) {
            $source = DB::table('DLV_configurations')->orderBy('id')->first();
            if ($source !== null) {
                if ($source->branch_id === null) {
                    DB::table('DLV_configurations')->where('id', $source->id)->update(['branch_id' => $primaryBranchId]);
                }
                $sourceBranchId = (int) (DB::table('DLV_configurations')->where('id', $source->id)->value('branch_id') ?? $primaryBranchId);
                $sourceZones = Schema::hasTable('DLV_zones')
                    ? DB::table('DLV_zones')->where('configuration_id', $source->id)->get()
                    : collect();

                foreach ($branchIds as $branchId) {
                    if ($branchId === $sourceBranchId) {
                        continue;
                    }
                    if (DB::table('DLV_configurations')->where('branch_id', $branchId)->exists()) {
                        continue;
                    }
                    $newConfigId = DB::table('DLV_configurations')->insertGetId([
                        'min_order_amount_kopecks' => $source->min_order_amount_kopecks,
                        'average_delivery_time_minutes' => $source->average_delivery_time_minutes,
                        'branch_id' => $branchId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    foreach ($sourceZones as $zone) {
                        DB::table('DLV_zones')->insert([
                            'configuration_id' => $newConfigId,
                            'zone_key' => $zone->zone_key,
                            'name' => $zone->name,
                            'fee_kopecks' => $zone->fee_kopecks,
                            'is_remote' => $zone->is_remote,
                            'geojson' => $zone->geojson,
                            'delivery_product_id' => $zone->delivery_product_id,
                            'sort_order' => $zone->sort_order,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Route sheets без филиала → primary.
        if (Schema::hasTable('DLV_route_sheets') && Schema::hasColumn('DLV_route_sheets', 'branch_id')) {
            DB::table('DLV_route_sheets')->whereNull('branch_id')->update(['branch_id' => $primaryBranchId]);
        }

        // PRM_zones snapshot: проставить branch_id primary (полный rebuild сделает listener).
        if (Schema::hasTable('PRM_zones') && Schema::hasColumn('PRM_zones', 'branch_id')) {
            DB::table('PRM_zones')->whereNull('branch_id')->update(['branch_id' => $primaryBranchId]);
        }
    }

    private function makeBranchNotNull(string $table, string $column): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            // SQLite: skip NOT NULL alter in tests if awkward; data already backfilled.
            return;
        }
        Schema::table($table, function (Blueprint $blueprint) use ($column): void {
            $blueprint->unsignedBigInteger($column)->nullable(false)->change();
        });
    }
};
