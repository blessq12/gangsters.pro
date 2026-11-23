<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('CMP_branch_categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('CMP_branch_categories', 'is_complement')) {
                $table->boolean('is_complement')->default(false)->after('is_accompanying');
            }
            if (! Schema::hasColumn('CMP_branch_categories', 'is_gift')) {
                $table->boolean('is_gift')->default(false)->after('is_complement');
            }
        });

        $this->migrateComplementCategories();
        $this->migrateGiftCategories();

        if (Schema::hasColumn('CAT_items', 'is_complement')) {
            Schema::table('CAT_items', function (Blueprint $table): void {
                $table->dropColumn('is_complement');
            });
        }

        if (Schema::hasColumn('CMP_branch_menu_items', 'is_gift_candidate')) {
            Schema::table('CMP_branch_menu_items', function (Blueprint $table): void {
                $table->dropColumn('is_gift_candidate');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('CAT_items', 'is_complement')) {
            Schema::table('CAT_items', function (Blueprint $table): void {
                $table->boolean('is_complement')->default(false);
            });
        }

        if (! Schema::hasColumn('CMP_branch_menu_items', 'is_gift_candidate')) {
            Schema::table('CMP_branch_menu_items', function (Blueprint $table): void {
                $table->boolean('is_gift_candidate')->default(false);
            });
        }

        Schema::table('CMP_branch_categories', function (Blueprint $table): void {
            if (Schema::hasColumn('CMP_branch_categories', 'is_gift')) {
                $table->dropColumn('is_gift');
            }
            if (Schema::hasColumn('CMP_branch_categories', 'is_complement')) {
                $table->dropColumn('is_complement');
            }
        });
    }

    private function migrateComplementCategories(): void
    {
        if (! Schema::hasTable('CAT_items') || ! Schema::hasColumn('CAT_items', 'is_complement')) {
            return;
        }

        $branchIds = DB::table('CMP_branches')->pluck('id');
        foreach ($branchIds as $branchId) {
            $branchId = (int) $branchId;
            $productIds = DB::table('CMP_branch_menu_items as m')
                ->join('CAT_items as c', 'c.id', '=', 'm.catalog_item_id')
                ->where('m.branch_id', $branchId)
                ->where('m.is_available', true)
                ->where('c.is_complement', true)
                ->pluck('m.catalog_item_id')
                ->map(static fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($productIds === []) {
                continue;
            }

            $categoryId = $this->ensureRoleCategory($branchId, 'complement', 'Комплектные', 'komplektnye');
            $this->syncCategoryProducts($categoryId, $productIds);
        }
    }

    private function migrateGiftCategories(): void
    {
        if (! Schema::hasTable('CMP_branch_menu_items')
            || ! Schema::hasColumn('CMP_branch_menu_items', 'is_gift_candidate')) {
            return;
        }

        $branchIds = DB::table('CMP_branches')->pluck('id');
        foreach ($branchIds as $branchId) {
            $branchId = (int) $branchId;
            $productIds = DB::table('CMP_branch_menu_items')
                ->where('branch_id', $branchId)
                ->where('is_gift_candidate', true)
                ->where('is_available', true)
                ->pluck('catalog_item_id')
                ->map(static fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($productIds === []) {
                continue;
            }

            $categoryId = $this->ensureRoleCategory($branchId, 'gift', 'Подарки', 'podarki');
            $this->syncCategoryProducts($categoryId, $productIds);
        }
    }

    private function ensureRoleCategory(int $branchId, string $role, string $name, string $slug): int
    {
        $column = $role === 'gift' ? 'is_gift' : 'is_complement';
        $existing = DB::table('CMP_branch_categories')
            ->where('branch_id', $branchId)
            ->where($column, true)
            ->first();

        if ($existing !== null) {
            return (int) $existing->id;
        }

        $sortOrder = (int) (DB::table('CMP_branch_categories')
            ->where('branch_id', $branchId)
            ->max('sort_order') ?? 0) + 10;

        $uniqueSlug = $slug;
        $n = 1;
        while (DB::table('CMP_branch_categories')
            ->where('branch_id', $branchId)
            ->where('slug', $uniqueSlug)
            ->exists()) {
            $uniqueSlug = $slug.'-'.$n;
            $n++;
        }

        $payload = [
            'branch_id' => $branchId,
            'name' => $name,
            'slug' => $uniqueSlug,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'is_accompanying' => false,
            'is_complement' => $role === 'complement',
            'is_gift' => $role === 'gift',
        ];
        if (Schema::hasColumn('CMP_branch_categories', 'created_at')) {
            $payload['created_at'] = now();
            $payload['updated_at'] = now();
        }

        return (int) DB::table('CMP_branch_categories')->insertGetId($payload);
    }

    /**
     * @param  list<int>  $productIds
     */
    private function syncCategoryProducts(int $categoryId, array $productIds): void
    {
        $existing = DB::table('CMP_branch_category_items')
            ->where('category_id', $categoryId)
            ->where('entry_kind', 'product')
            ->pluck('entry_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $existingSet = array_fill_keys($existing, true);
        $sort = (int) (DB::table('CMP_branch_category_items')
            ->where('category_id', $categoryId)
            ->max('sort_order') ?? 0);

        foreach ($productIds as $productId) {
            if (isset($existingSet[$productId])) {
                continue;
            }
            $sort += 1;
            $row = [
                'category_id' => $categoryId,
                'entry_kind' => 'product',
                'entry_id' => $productId,
                'sort_order' => $sort,
            ];
            if (Schema::hasColumn('CMP_branch_category_items', 'created_at')) {
                $row['created_at'] = now();
                $row['updated_at'] = now();
            }
            DB::table('CMP_branch_category_items')->insert($row);
        }
    }
};
