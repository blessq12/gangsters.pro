<?php

namespace Database\Seeders;

use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Company\Model\CMP_Branch;
use App\Infrastructure\Company\Model\CMP_BranchCategory;
use App\Infrastructure\Company\Model\CMP_BranchCategoryItem;
use App\Infrastructure\Company\Model\CMP_BranchMenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Меню филиала: все active товары каталога на каждый активный филиал с ценой 0
 * (цены — в админке; существующие цены и доступность не перезаписываются).
 *
 * Теги и сеты не сидятся — их наполняет админка.
 * Категории: если в филиале категорий ещё нет — создаётся одна плейсхолдер-категория «Меню»
 * со всеми товарами. Если категории уже есть — структура не трогается.
 */
class BranchMenuSeeder extends Seeder
{
    private const PLACEHOLDER_CATEGORY_NAME = 'Меню';

    private const PLACEHOLDER_CATEGORY_SLUG = 'menu';

    public function run(): void
    {
        $branchIds = CMP_Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($branchIds === []) {
            throw new RuntimeException('Нет активных филиалов: сначала запустите CompanySeeder.');
        }

        $itemIds = CAT_Item::query()
            ->where('status', ProductStatus::Active->value)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($itemIds === []) {
            throw new RuntimeException('Нет позиций каталога: сначала запустите CatalogSeeder.');
        }

        DB::transaction(function () use ($branchIds, $itemIds): void {
            foreach ($branchIds as $branchId) {
                $this->seedMenuItems((int) $branchId, $itemIds);
                $this->seedPlaceholderCategory((int) $branchId, $itemIds);
            }
        });
    }

    /**
     * @param  list<int>  $itemIds
     */
    private function seedMenuItems(int $branchId, array $itemIds): void
    {
        foreach ($itemIds as $itemId) {
            CMP_BranchMenuItem::query()->firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'catalog_item_id' => $itemId,
                ],
                [
                    'is_available' => true,
                    'price_kopecks' => 0,
                ],
            );
        }
    }

    /**
     * @param  list<int>  $itemIds
     */
    private function seedPlaceholderCategory(int $branchId, array $itemIds): void
    {
        if (CMP_BranchCategory::query()->where('branch_id', $branchId)->exists()) {
            return;
        }

        $category = CMP_BranchCategory::query()->create([
            'branch_id' => $branchId,
            'name' => self::PLACEHOLDER_CATEGORY_NAME,
            'slug' => self::PLACEHOLDER_CATEGORY_SLUG,
            'sort_order' => 0,
            'is_active' => true,
            'is_accompanying' => false,
        ]);

        foreach ($itemIds as $sortOrder => $itemId) {
            CMP_BranchCategoryItem::query()->create([
                'category_id' => $category->id,
                'entry_kind' => BranchMenuEntryKind::Product,
                'entry_id' => $itemId,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
