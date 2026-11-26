<?php

namespace App\Application\Menu\Command;

use App\Domain\Supporting\Menu\Entity\BranchCategory;
use App\Domain\Supporting\Menu\Entity\BranchCategoryItem;
use App\Domain\Supporting\Menu\Entity\BranchMenuItem;
use App\Domain\Supporting\Menu\Entity\BranchSet;
use App\Domain\Supporting\Menu\Entity\BranchTag;
use App\Domain\Supporting\Menu\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Menu\Repository\BranchMenuRepository;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;
use App\Domain\Supporting\Menu\Repository\BranchTagRepository;

/**
 * Клонирование меню филиала (позиции + категории/теги/сеты).
 */
final class CloneBranchMenuUseCase
{
    private const MODE_OVERWRITE = 'overwrite';

    private const MODE_SKIP = 'skip_existing';

    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchMenuRepository $menu,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchTagRepository $tags,
        private readonly BranchSetRepository $sets,
    ) {}

    /**
     * @param  list<int>  $targetBranchIds
     * @return list<array{
     *     branch_id: int,
     *     menu: array{copied: int, skipped: int},
     *     structure: array{categories: int, tags: int, sets: int, skipped: bool}
     * }>
     */
    public function execute(int $sourceBranchId, array $targetBranchIds, string $mode): array
    {
        $mode = $this->normalizeMode($mode);
        if ($sourceBranchId < 1) {
            throw new \InvalidArgumentException('Некорректный source_branch_id.');
        }
        $this->branches->assertBranchExists($sourceBranchId);

        $targets = $this->normalizeTargets($sourceBranchId, $targetBranchIds);
        $sourceMenu = $this->menu->listByBranch($sourceBranchId);
        $sourceStructure = $this->loadSourceStructure($sourceBranchId);

        $results = [];
        foreach ($targets as $targetId) {
            $results[] = [
                'branch_id' => $targetId,
                'menu' => $this->cloneMenu($targetId, $mode, $sourceMenu),
                'structure' => $this->cloneStructure($targetId, $mode, $sourceStructure),
            ];
        }

        return $results;
    }

    /**
     * @param  list<BranchMenuItem>  $sourceMenu
     * @return array{copied: int, skipped: int}
     */
    private function cloneMenu(int $targetId, string $mode, array $sourceMenu): array
    {
        $existing = $this->menu->findByBranchAndItemIds(
            $targetId,
            array_map(static fn (BranchMenuItem $item): int => $item->catalogItemId(), $sourceMenu),
        );

        $toUpsert = [];
        $skipped = 0;

        foreach ($sourceMenu as $item) {
            $catalogItemId = $item->catalogItemId();
            if ($mode === self::MODE_SKIP && isset($existing[$catalogItemId])) {
                $skipped++;

                continue;
            }

            $toUpsert[] = new BranchMenuItem(
                branchId: $targetId,
                catalogItemId: $catalogItemId,
                isAvailable: $item->isAvailable(),
                priceKopecks: $item->priceKopecks(),
            );
        }

        if ($toUpsert !== []) {
            $this->menu->upsertMany($targetId, $toUpsert);
        }

        return ['copied' => count($toUpsert), 'skipped' => $skipped];
    }

    /**
     * @return array{
     *     categories: list<BranchCategory>,
     *     items: array<int, list<BranchCategoryItem>>,
     *     tags: list<BranchTag>,
     *     sets: list<BranchSet>,
     *     productTagIds: array<int, list<int>>
     * }
     */
    private function loadSourceStructure(int $sourceId): array
    {
        $categories = $this->categories->listByBranch($sourceId);
        $items = [];
        $productIds = [];
        foreach ($categories as $category) {
            $items[$category->id()] = $this->categories->findItemsByCategoryId($category->id());
            foreach ($items[$category->id()] as $item) {
                if ($item->entryKind() === BranchMenuEntryKind::Product) {
                    $productIds[$item->entryId()] = $item->entryId();
                }
            }
        }
        foreach ($this->menu->listByBranch($sourceId) as $menuItem) {
            $productIds[$menuItem->catalogItemId()] = $menuItem->catalogItemId();
        }

        return [
            'categories' => $categories,
            'items' => $items,
            'tags' => $this->tags->listByBranch($sourceId),
            'sets' => $this->sets->listByBranch($sourceId),
            'productTagIds' => $this->tags->findTagIdsForEntries(
                BranchMenuEntryKind::Product,
                array_values($productIds),
            ),
        ];
    }

    /**
     * @param  array{categories: list<BranchCategory>, items: array<int, list<BranchCategoryItem>>, tags: list<BranchTag>, sets: list<BranchSet>, productTagIds: array<int, list<int>>}  $source
     * @return array{categories: int, tags: int, sets: int, skipped: bool}
     */
    private function cloneStructure(int $targetId, string $mode, array $source): array
    {
        if ($mode === self::MODE_SKIP && $this->targetHasStructure($targetId)) {
            return ['categories' => 0, 'tags' => 0, 'sets' => 0, 'skipped' => true];
        }

        /** @var array<int, int> $tagMap oldTagId => newTagId */
        $tagMap = $this->tags->replaceAllForBranch($targetId, $source['tags']);

        $sets = [];
        foreach ($source['sets'] as $set) {
            $sets[] = new BranchSet(
                id: $set->id(),
                branchId: $targetId,
                name: $set->name(),
                slug: $set->slug(),
                description: $set->description(),
                isAvailable: $set->isAvailable(),
                priceKopecks: $set->priceKopecks(),
                status: $set->status(),
                lines: $set->lines(),
                tagIds: $this->remapIds($set->tagIds(), $tagMap),
            );
        }

        /** @var array<int, int> $setMap oldSetId => newSetId */
        $setMap = $this->sets->replaceAllForBranch($targetId, $sets);

        $categories = [];
        $itemsByCategoryId = [];
        foreach ($source['categories'] as $category) {
            $categories[] = new BranchCategory(
                id: $category->id(),
                branchId: $targetId,
                name: $category->name(),
                slug: $category->slug(),
                sortOrder: $category->sortOrder(),
                isActive: $category->isActive(),
                isAccompanying: $category->isAccompanying(),
                isComplement: $category->isComplement(),
                isGift: $category->isGift(),
            );

            $remapped = [];
            foreach ($source['items'][$category->id()] ?? [] as $item) {
                $entryId = $item->entryId();
                if ($item->entryKind() === BranchMenuEntryKind::Set) {
                    $entryId = $setMap[$entryId] ?? 0;
                    if ($entryId < 1) {
                        continue;
                    }
                }
                $remapped[] = new BranchCategoryItem(
                    categoryId: $category->id(),
                    entryKind: $item->entryKind(),
                    entryId: $entryId,
                    sortOrder: $item->sortOrder(),
                );
            }
            $itemsByCategoryId[$category->id()] = $remapped;
        }
        $this->categories->replaceAllForBranch($targetId, $categories, $itemsByCategoryId);

        foreach ($source['productTagIds'] as $productId => $oldTagIds) {
            $this->tags->syncEntryTags(
                $targetId,
                BranchMenuEntryKind::Product,
                $productId,
                $this->remapIds($oldTagIds, $tagMap),
            );
        }

        return [
            'categories' => count($categories),
            'tags' => count($tagMap),
            'sets' => count($setMap),
            'skipped' => false,
        ];
    }

    private function targetHasStructure(int $targetId): bool
    {
        return $this->categories->listByBranch($targetId) !== []
            || $this->tags->listByBranch($targetId) !== []
            || $this->sets->listByBranch($targetId) !== [];
    }

    /**
     * @param  list<int>  $ids
     * @param  array<int, int>  $map
     * @return list<int>
     */
    private function remapIds(array $ids, array $map): array
    {
        $out = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $out[$map[$id]] = $map[$id];
            }
        }

        return array_values($out);
    }

    private function normalizeMode(string $mode): string
    {
        $normalized = trim($mode) === '' ? self::MODE_OVERWRITE : trim($mode);
        if (! in_array($normalized, [self::MODE_OVERWRITE, self::MODE_SKIP], true)) {
            throw new \InvalidArgumentException('mode должен быть overwrite или skip_existing.');
        }

        return $normalized;
    }

    /**
     * @param  list<int>  $rawIds
     * @return list<int>
     */
    private function normalizeTargets(int $sourceId, array $rawIds): array
    {
        $ids = [];
        foreach ($rawIds as $rawId) {
            $id = (int) $rawId;
            if ($id < 1) {
                throw new \InvalidArgumentException('Некорректный target_branch_id.');
            }
            if ($id === $sourceId) {
                throw new \InvalidArgumentException('Нельзя клонировать филиал сам в себя.');
            }
            $this->branches->assertBranchExists($id);
            $ids[$id] = $id;
        }

        if ($ids === []) {
            throw new \InvalidArgumentException('Нужен хотя бы один целевой филиал.');
        }

        return array_values($ids);
    }
}
