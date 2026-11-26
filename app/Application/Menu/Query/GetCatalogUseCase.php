<?php

namespace App\Application\Menu\Query;

use App\Domain\Supporting\Menu\Entity\BranchCategory;
use App\Domain\Supporting\Menu\Entity\BranchMenuItem;
use App\Domain\Supporting\Menu\Entity\BranchSet;
use App\Domain\Supporting\Menu\Entity\BranchTag;
use App\Domain\Supporting\Menu\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Port\MenuCatalogLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Menu\Repository\BranchMenuRepository;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;
use App\Domain\Supporting\Menu\Repository\BranchTagRepository;
use App\Shared\ValueObject\Money;

/**
 * Публичное меню филиала: категории + товары/сеты с ценами филиала.
 * Catalog — только через MenuCatalogLookupPort.
 */
final class GetCatalogUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchTagRepository $tags,
        private readonly BranchSetRepository $sets,
        private readonly BranchMenuRepository $menu,
        private readonly MenuCatalogLookupPort $catalog,
    ) {}

    /**
     * @return array{
     *     categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     accompanying_categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     complement_products: list<array<string, mixed>>
     * }
     */
    public function execute(int $branchId): array
    {
        $this->branches->assertBranchActive($branchId);

        $menuByProductId = $this->indexMenu($this->menu->listAvailableByBranch($branchId));
        $tagById = $this->indexTags($this->tags->listActiveOrderedByBranch($branchId));

        $menuCategories = [];
        $accompanying = [];
        $complementProducts = [];

        foreach ($this->categories->listActiveOrderedByBranch($branchId) as $category) {
            $node = $this->buildCategoryNode($branchId, $category, $tagById, $menuByProductId);
            if ($node['items'] === []) {
                continue;
            }
            if ($category->isComplement()) {
                foreach ($node['items'] as $item) {
                    if (($item['kind'] ?? '') === 'product' || ! isset($item['kind'])) {
                        $complementProducts[] = $item;
                    }
                }

                continue;
            }
            if ($category->isGift()) {
                continue;
            }
            if ($category->isAccompanying()) {
                $accompanying[] = $node;
            } else {
                $menuCategories[] = $node;
            }
        }

        return [
            'categories' => $menuCategories,
            'accompanying_categories' => $accompanying,
            'complement_products' => $complementProducts,
        ];
    }

    /**
     * @param  array<int, BranchTag>  $tagById
     * @param  array<int, BranchMenuItem>  $menuByProductId
     * @return array{category: array<string, mixed>, items: list<array<string, mixed>>}
     */
    private function buildCategoryNode(
        int $branchId,
        BranchCategory $category,
        array $tagById,
        array $menuByProductId,
    ): array {
        $links = $this->categories->findItemsByCategoryId($category->id());

        $productIds = [];
        $setIds = [];
        foreach ($links as $link) {
            if ($link->entryKind() === BranchMenuEntryKind::Set) {
                $setIds[] = $link->entryId();
            } else {
                if (! isset($menuByProductId[$link->entryId()])) {
                    continue;
                }
                $productIds[] = $link->entryId();
            }
        }

        $productsById = $this->catalog->presentActiveProductsByIds($productIds);
        $setsById = $this->indexSets($this->sets->findAvailableByBranchAndIds($branchId, $setIds));

        $productTagIds = $this->tags->findTagIdsForEntries(BranchMenuEntryKind::Product, $productIds);
        $setTagIds = $this->tags->findTagIdsForEntries(BranchMenuEntryKind::Set, $setIds);

        $lineProductIds = [];
        foreach ($setsById as $set) {
            foreach ($set->lines() as $line) {
                $lineProductIds[] = $line->catalogItemId();
            }
        }
        $lineProductIds = array_values(array_unique($lineProductIds));
        $lineNames = $this->catalog->findProductNamesByIds($lineProductIds);
        $lineWeights = $this->catalog->findProductWeightsByIds($lineProductIds);

        $items = [];
        foreach ($links as $link) {
            if ($link->entryKind() === BranchMenuEntryKind::Set) {
                $set = $setsById[$link->entryId()] ?? null;
                if (! $set instanceof BranchSet || $set->lines() === [] || ! $set->isAvailable()) {
                    continue;
                }
                $tags = $this->resolveTags($setTagIds[$set->id()] ?? [], $tagById);
                $items[] = $this->presentSet($set, $tags, $lineNames, $lineWeights);

                continue;
            }

            $menuItem = $menuByProductId[$link->entryId()] ?? null;
            $product = $productsById[$link->entryId()] ?? null;
            if ($menuItem === null || $product === null) {
                continue;
            }
            $tags = $this->resolveTags($productTagIds[$link->entryId()] ?? [], $tagById);
            $product['tags'] = $tags;
            $product['price'] = [
                'amount' => intdiv($menuItem->priceKopecks(), 100),
                'currency' => Money::CURRENCY,
            ];
            $items[] = $product;
        }

        return [
            'category' => [
                'id' => $category->id(),
                'name' => $category->name(),
                'slug' => $category->slug(),
                'sort_order' => $category->sortOrder(),
                'is_active' => $category->isActive(),
                'is_accompanying' => $category->isAccompanying(),
                'is_complement' => $category->isComplement(),
                'is_gift' => $category->isGift(),
            ],
            'items' => $items,
        ];
    }

    /**
     * @param  list<array{code: string, label: string, color: string}>  $tags
     * @param  array<int, string>  $lineNames
     * @param  array<int, int|null>  $lineWeights
     * @return array<string, mixed>
     */
    private function presentSet(BranchSet $set, array $tags, array $lineNames, array $lineWeights): array
    {
        $composition = [];
        foreach ($set->lines() as $line) {
            $composition[] = [
                'product_id' => $line->catalogItemId(),
                'name' => $lineNames[$line->catalogItemId()] ?? '',
                'quantity' => $line->quantity(),
                'weight' => $lineWeights[$line->catalogItemId()] ?? null,
            ];
        }

        return [
            'id' => $set->id(),
            'kind' => 'set',
            'name' => $set->name(),
            'slug' => $set->slug(),
            'description' => $set->description(),
            'status' => $set->status(),
            'tags' => $tags,
            'composition' => $composition,
            'lines' => $composition,
            'price' => [
                'amount' => intdiv($set->priceKopecks(), 100),
                'currency' => Money::CURRENCY,
            ],
        ];
    }

    /**
     * @param  list<int>  $tagIds
     * @param  array<int, BranchTag>  $tagById
     * @return list<array{code: string, label: string, color: string}>
     */
    private function resolveTags(array $tagIds, array $tagById): array
    {
        $tags = [];
        foreach ($tagIds as $tagId) {
            $tag = $tagById[$tagId] ?? null;
            if ($tag === null || ! $tag->isActive()) {
                continue;
            }
            $tags[] = [
                'code' => $tag->code(),
                'label' => $tag->label(),
                'color' => $tag->color(),
            ];
        }

        return $tags;
    }

    /**
     * @param  list<BranchMenuItem>  $items
     * @return array<int, BranchMenuItem>
     */
    private function indexMenu(array $items): array
    {
        $indexed = [];
        foreach ($items as $item) {
            $indexed[$item->catalogItemId()] = $item;
        }

        return $indexed;
    }

    /**
     * @param  list<BranchTag>  $tags
     * @return array<int, BranchTag>
     */
    private function indexTags(array $tags): array
    {
        $indexed = [];
        foreach ($tags as $tag) {
            $indexed[$tag->id()] = $tag;
        }

        return $indexed;
    }

    /**
     * @param  list<BranchSet>  $sets
     * @return array<int, BranchSet>
     */
    private function indexSets(array $sets): array
    {
        $indexed = [];
        foreach ($sets as $set) {
            $indexed[$set->id()] = $set;
        }

        return $indexed;
    }
}
