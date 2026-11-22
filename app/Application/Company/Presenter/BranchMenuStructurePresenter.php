<?php

namespace App\Application\Company\Presenter;

use App\Domain\Supporting\Company\Entity\BranchCategory;
use App\Domain\Supporting\Company\Entity\BranchCategoryItem;
use App\Domain\Supporting\Company\Entity\BranchSet;
use App\Domain\Supporting\Company\Entity\BranchTag;
use App\Domain\Supporting\Company\ValueObject\BranchSetLine;

/**
 * Формат ответа категорий, тегов и наборов филиала для админки.
 */
final class BranchMenuStructurePresenter
{
    /**
     * @param  list<BranchCategoryItem>  $items
     * @return array<string, mixed>
     */
    public function category(BranchCategory $category, array $items): array
    {
        return [
            'id' => $category->id(),
            'branch_id' => $category->branchId(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'sort_order' => $category->sortOrder(),
            'is_active' => $category->isActive(),
            'is_accompanying' => $category->isAccompanying(),
            'items' => array_map(
                static fn (BranchCategoryItem $item): array => [
                    'entry_kind' => $item->entryKind()->value,
                    'entry_id' => $item->entryId(),
                    'sort_order' => $item->sortOrder(),
                ],
                $items,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tag(BranchTag $tag): array
    {
        return [
            'id' => $tag->id(),
            'branch_id' => $tag->branchId(),
            'code' => $tag->code(),
            'label' => $tag->label(),
            'color' => $tag->color(),
            'is_active' => $tag->isActive(),
            'sort_order' => $tag->sortOrder(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function set(BranchSet $set): array
    {
        return [
            'id' => $set->id(),
            'branch_id' => $set->branchId(),
            'name' => $set->name(),
            'slug' => $set->slug(),
            'description' => $set->description(),
            'is_available' => $set->isAvailable(),
            'price_kopecks' => $set->priceKopecks(),
            'price_rubles' => intdiv($set->priceKopecks(), 100),
            'status' => $set->status(),
            'lines' => array_map(
                static fn (BranchSetLine $line): array => [
                    'catalog_item_id' => $line->catalogItemId(),
                    'quantity' => $line->quantity(),
                ],
                $set->lines(),
            ),
            'tag_ids' => $set->tagIds(),
        ];
    }
}
