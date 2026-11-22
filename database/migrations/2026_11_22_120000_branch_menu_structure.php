<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Категории / теги / сеты переезжают в меню филиала.
 * Catalog остаётся только справочником товаров.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createBranchStructureTables();
        $this->migrateFromCatalog();
        $this->dropCatalogStructure();
        $this->slimCatalogItems();
    }

    public function down(): void
    {
        Schema::dropIfExists('CMP_branch_entry_tags');
        Schema::dropIfExists('CMP_branch_category_items');
        Schema::dropIfExists('CMP_branch_set_lines');
        Schema::dropIfExists('CMP_branch_sets');
        Schema::dropIfExists('CMP_branch_tags');
        Schema::dropIfExists('CMP_branch_categories');
    }

    private function createBranchStructureTables(): void
    {
        if (! Schema::hasTable('CMP_branch_categories')) {
            Schema::create('CMP_branch_categories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->string('name');
                $table->string('slug');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_accompanying')->default(false);
                $table->timestamps();

                $table->index('branch_id');
                $table->unique(['branch_id', 'slug']);
                $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('CMP_branch_sets')) {
            Schema::create('CMP_branch_sets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->boolean('is_available')->default(true);
                $table->unsignedInteger('price_kopecks')->default(0);
                $table->string('status', 32)->default('active');
                $table->timestamps();

                $table->index('branch_id');
                $table->unique(['branch_id', 'slug']);
                $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('CMP_branch_set_lines')) {
            Schema::create('CMP_branch_set_lines', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('set_id');
                $table->unsignedBigInteger('catalog_item_id');
                $table->unsignedInteger('quantity');

                $table->foreign('set_id')->references('id')->on('CMP_branch_sets')->cascadeOnDelete();
                $table->foreign('catalog_item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
                $table->unique(['set_id', 'catalog_item_id']);
            });
        }

        if (! Schema::hasTable('CMP_branch_category_items')) {
            Schema::create('CMP_branch_category_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->string('entry_kind', 16);
                $table->unsignedBigInteger('entry_id');
                $table->unsignedInteger('sort_order')->default(0);

                $table->foreign('category_id')->references('id')->on('CMP_branch_categories')->cascadeOnDelete();
                $table->unique(['category_id', 'entry_kind', 'entry_id'], 'cmp_branch_cat_entry_uq');
                $table->index(['entry_kind', 'entry_id']);
            });
        }

        if (! Schema::hasTable('CMP_branch_tags')) {
            Schema::create('CMP_branch_tags', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->string('code');
                $table->string('label');
                $table->string('color', 32);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('branch_id');
                $table->unique(['branch_id', 'code']);
                $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('CMP_branch_entry_tags')) {
            Schema::create('CMP_branch_entry_tags', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tag_id');
                $table->string('entry_kind', 16);
                $table->unsignedBigInteger('entry_id');

                $table->foreign('tag_id')->references('id')->on('CMP_branch_tags')->cascadeOnDelete();
                $table->unique(['tag_id', 'entry_kind', 'entry_id'], 'cmp_branch_entry_tag_uq');
                $table->index(['entry_kind', 'entry_id']);
            });
        }
    }

    private function migrateFromCatalog(): void
    {
        if (! Schema::hasTable('CMP_branches') || ! Schema::hasTable('CAT_items')) {
            return;
        }

        $branchIds = DB::table('CMP_branches')->orderBy('id')->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
        if ($branchIds === []) {
            return;
        }

        $prices = [];
        if (Schema::hasTable('CAT_prices')) {
            $prices = DB::table('CAT_prices')
                ->where('type', 'regular')
                ->pluck('amount_kopecks', 'item_id')
                ->map(static fn (mixed $v): int => (int) $v)
                ->all();
        }

        $setItems = [];
        if (Schema::hasColumn('CAT_items', 'kind')) {
            $setItems = DB::table('CAT_items')->where('kind', 'set')->orderBy('id')->get()->all();
        }

        $setLinesBySet = [];
        if (Schema::hasTable('CAT_set_lines')) {
            foreach (DB::table('CAT_set_lines')->get() as $line) {
                $setLinesBySet[(int) $line->set_item_id][] = $line;
            }
        }

        $categories = Schema::hasTable('CAT_categories')
            ? DB::table('CAT_categories')->orderBy('sort_order')->orderBy('id')->get()->all()
            : [];
        $categoryItems = Schema::hasTable('CAT_category_items')
            ? DB::table('CAT_category_items')->orderBy('sort_order')->orderBy('id')->get()->all()
            : [];
        $tags = Schema::hasTable('CAT_tags')
            ? DB::table('CAT_tags')->orderBy('sort_order')->orderBy('id')->get()->all()
            : [];
        $itemTags = Schema::hasTable('CAT_item_tags')
            ? DB::table('CAT_item_tags')->get()->all()
            : [];

        $itemKind = [];
        if (Schema::hasColumn('CAT_items', 'kind')) {
            foreach (DB::table('CAT_items')->get(['id', 'kind']) as $row) {
                $itemKind[(int) $row->id] = (string) $row->kind;
            }
        }

        foreach ($branchIds as $branchId) {
            $menuProductIds = [];
            if (Schema::hasTable('CMP_branch_menu_items')) {
                $menuProductIds = DB::table('CMP_branch_menu_items')
                    ->where('branch_id', $branchId)
                    ->pluck('catalog_item_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all();
                $menuProductIds = array_fill_keys($menuProductIds, true);
            }

            /** @var array<int, int> $oldSetIdToNew */
            $oldSetIdToNew = [];
            foreach ($setItems as $set) {
                $oldId = (int) $set->id;
                $menuPrice = null;
                if (Schema::hasTable('CMP_branch_menu_items')) {
                    $menuRow = DB::table('CMP_branch_menu_items')
                        ->where('branch_id', $branchId)
                        ->where('catalog_item_id', $oldId)
                        ->first();
                    if ($menuRow !== null) {
                        $menuPrice = (int) $menuRow->price_kopecks;
                    }
                }

                $newSetId = DB::table('CMP_branch_sets')->insertGetId([
                    'branch_id' => $branchId,
                    'name' => (string) $set->name,
                    'slug' => (string) $set->slug.'-b'.$branchId,
                    'description' => $set->description,
                    'is_available' => $menuPrice !== null ? (bool) ($menuRow->is_available ?? true) : true,
                    'price_kopecks' => $menuPrice ?? (int) ($prices[$oldId] ?? 0),
                    'status' => (string) ($set->status ?? 'active'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $oldSetIdToNew[$oldId] = $newSetId;

                foreach ($setLinesBySet[$oldId] ?? [] as $line) {
                    $componentId = (int) $line->component_item_id;
                    if (($itemKind[$componentId] ?? 'product') === 'set') {
                        continue;
                    }
                    DB::table('CMP_branch_set_lines')->insert([
                        'set_id' => $newSetId,
                        'catalog_item_id' => $componentId,
                        'quantity' => max(1, (int) $line->quantity),
                    ]);
                }

                if (Schema::hasTable('CMP_branch_menu_items')) {
                    DB::table('CMP_branch_menu_items')
                        ->where('branch_id', $branchId)
                        ->where('catalog_item_id', $oldId)
                        ->delete();
                }
            }

            /** @var array<int, int> $oldTagIdToNew */
            $oldTagIdToNew = [];
            foreach ($tags as $tag) {
                $oldTagId = (int) $tag->id;
                $newTagId = DB::table('CMP_branch_tags')->insertGetId([
                    'branch_id' => $branchId,
                    'code' => (string) $tag->code,
                    'label' => (string) $tag->label,
                    'color' => (string) $tag->color,
                    'is_active' => (bool) $tag->is_active,
                    'sort_order' => (int) $tag->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $oldTagIdToNew[$oldTagId] = $newTagId;
            }

            foreach ($itemTags as $link) {
                $oldItemId = (int) $link->item_id;
                $oldTagId = (int) $link->tag_id;
                $newTagId = $oldTagIdToNew[$oldTagId] ?? null;
                if ($newTagId === null) {
                    continue;
                }
                $kind = $itemKind[$oldItemId] ?? 'product';
                if ($kind === 'set') {
                    $entryId = $oldSetIdToNew[$oldItemId] ?? null;
                    $entryKind = 'set';
                } else {
                    if (! isset($menuProductIds[$oldItemId])) {
                        continue;
                    }
                    $entryId = $oldItemId;
                    $entryKind = 'product';
                }
                if ($entryId === null) {
                    continue;
                }
                DB::table('CMP_branch_entry_tags')->insert([
                    'tag_id' => $newTagId,
                    'entry_kind' => $entryKind,
                    'entry_id' => $entryId,
                ]);
            }

            /** @var array<int, int> $oldCategoryIdToNew */
            $oldCategoryIdToNew = [];
            foreach ($categories as $category) {
                $oldCatId = (int) $category->id;
                $newCatId = DB::table('CMP_branch_categories')->insertGetId([
                    'branch_id' => $branchId,
                    'name' => (string) $category->name,
                    'slug' => (string) $category->slug.'-b'.$branchId,
                    'sort_order' => (int) $category->sort_order,
                    'is_active' => (bool) $category->is_active,
                    'is_accompanying' => (bool) ($category->is_accompanying ?? false),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $oldCategoryIdToNew[$oldCatId] = $newCatId;
            }

            foreach ($categoryItems as $link) {
                $oldCatId = (int) $link->category_id;
                $newCatId = $oldCategoryIdToNew[$oldCatId] ?? null;
                if ($newCatId === null) {
                    continue;
                }
                $oldItemId = (int) $link->item_id;
                $kind = $itemKind[$oldItemId] ?? 'product';
                if ($kind === 'set') {
                    $entryId = $oldSetIdToNew[$oldItemId] ?? null;
                    $entryKind = 'set';
                } else {
                    if (! isset($menuProductIds[$oldItemId])) {
                        continue;
                    }
                    $entryId = $oldItemId;
                    $entryKind = 'product';
                }
                if ($entryId === null) {
                    continue;
                }
                DB::table('CMP_branch_category_items')->insert([
                    'category_id' => $newCatId,
                    'entry_kind' => $entryKind,
                    'entry_id' => $entryId,
                    'sort_order' => (int) $link->sort_order,
                ]);
            }
        }
    }

    private function dropCatalogStructure(): void
    {
        Schema::dropIfExists('CAT_set_lines');
        Schema::dropIfExists('CAT_item_tags');
        Schema::dropIfExists('CAT_tags');
        Schema::dropIfExists('CAT_category_items');
        Schema::dropIfExists('CAT_categories');
        Schema::dropIfExists('CAT_prices');

        if (Schema::hasColumn('CAT_items', 'kind')) {
            $setIds = DB::table('CAT_items')->where('kind', 'set')->pluck('id')->all();
            if ($setIds !== []) {
                if (Schema::hasTable('CAT_item_images')) {
                    DB::table('CAT_item_images')->whereIn('item_id', $setIds)->delete();
                }
                if (Schema::hasTable('CMP_branch_menu_items')) {
                    DB::table('CMP_branch_menu_items')->whereIn('catalog_item_id', $setIds)->delete();
                }
                DB::table('CAT_items')->whereIn('id', $setIds)->delete();
            }
        }
    }

    private function slimCatalogItems(): void
    {
        if (Schema::hasColumn('CAT_items', 'kind')) {
            Schema::table('CAT_items', function (Blueprint $table): void {
                $table->dropColumn('kind');
            });
        }
    }
};
