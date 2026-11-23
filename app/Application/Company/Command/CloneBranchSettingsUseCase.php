<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Domain\Supporting\Company\Entity\BranchCategory;
use App\Domain\Supporting\Company\Entity\BranchCategoryItem;
use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Domain\Supporting\Company\Entity\BranchSet;
use App\Domain\Supporting\Company\Entity\BranchTag;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Company\Port\CompanyDeliveryConfigClonePort;
use App\Domain\Supporting\Company\Port\CompanyPromotionPolicyClonePort;
use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;
use App\Domain\Supporting\Company\Repository\BranchTagRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Копирует меню (позиции + категории/теги/сеты) / политики / доставку с одного филиала на один или несколько.
 * Catalog не трогает. Заказы не трогает. Режим overwrite | skip_existing.
 *
 * Структура меню (категории/теги/сеты) в skip_existing копируется только в филиал,
 * где её ещё нет вообще — частичный перенос ломает ремап id сетов.
 */
final class CloneBranchSettingsUseCase
{
    private const MODE_OVERWRITE = 'overwrite';

    private const MODE_SKIP = 'skip_existing';

    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchMenuRepository $menu,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchTagRepository $tags,
        private readonly BranchSetRepository $sets,
        private readonly CompanyPromotionPolicyClonePort $promotionClone,
        private readonly CompanyDeliveryConfigClonePort $deliveryClone,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array{
     *     source_branch_id: int,
     *     mode: string,
     *     targets: list<array{
     *         branch_id: int,
     *         menu: array{copied: int, skipped: int},
     *         structure: array{categories: int, tags: int, sets: int, skipped: bool},
     *         promotions: array{policy: string, roles_copied: int, roles_skipped: int},
     *         delivery: string
     *     }>
     * }
     */
    public function execute(CloneBranchSettingsDto $dto): array
    {
        $mode = $this->normalizeMode($dto->mode);
        $sourceId = $dto->sourceBranchId;
        if ($sourceId < 1) {
            throw new \InvalidArgumentException('Некорректный source_branch_id.');
        }
        if ($this->branches->findById($sourceId) === null) {
            throw new \InvalidArgumentException('Филиал-источник не найден.');
        }

        $targets = $this->normalizeTargets($sourceId, $dto->targetBranchIds);
        if ($targets === []) {
            throw new \InvalidArgumentException('Нужен хотя бы один целевой филиал.');
        }

        if (! $dto->copyMenu && ! $dto->copyPromotions && ! $dto->copyDelivery) {
            throw new \InvalidArgumentException('Выбери хотя бы один блок для копирования.');
        }

        return $this->transaction->run(function () use ($dto, $mode, $sourceId, $targets): array {
            $sourceMenu = $dto->copyMenu ? $this->menu->listByBranch($sourceId) : [];
            $sourceStructure = $dto->copyMenu ? $this->loadSourceStructure($sourceId) : null;
            $sourcePolicyFields = $dto->copyPromotions
                ? $this->promotionClone->exportFlatFields($sourceId)
                : null;
            $sourceDeliverySnapshot = $dto->copyDelivery
                ? $this->deliveryClone->export($sourceId)
                : null;

            if ($dto->copyPromotions && $sourcePolicyFields === null) {
                throw new \InvalidArgumentException('У филиала-источника нет политики акций.');
            }
            if ($dto->copyDelivery && $sourceDeliverySnapshot === null) {
                throw new \InvalidArgumentException('У филиала-источника нет настроек доставки.');
            }

            $results = [];
            foreach ($targets as $targetId) {
                $results[] = $this->cloneToTarget(
                    targetId: $targetId,
                    mode: $mode,
                    copyMenu: $dto->copyMenu,
                    copyPromotions: $dto->copyPromotions,
                    copyDelivery: $dto->copyDelivery,
                    sourceMenu: $sourceMenu,
                    sourceStructure: $sourceStructure,
                    sourcePolicyFields: $sourcePolicyFields,
                    sourceDeliverySnapshot: $sourceDeliverySnapshot,
                );
            }

            return [
                'source_branch_id' => $sourceId,
                'mode' => $mode,
                'targets' => $results,
            ];
        });
    }

    /**
     * @param  list<BranchMenuItem>  $sourceMenu
     * @param  array{categories: list<BranchCategory>, items: array<int, list<BranchCategoryItem>>, tags: list<BranchTag>, sets: list<BranchSet>, productTagIds: array<int, list<int>>}|null  $sourceStructure
     * @return array{
     *     branch_id: int,
     *     menu: array{copied: int, skipped: int},
     *     structure: array{categories: int, tags: int, sets: int, skipped: bool},
     *     promotions: array{policy: string, roles_copied: int, roles_skipped: int},
     *     delivery: string
     * }
     */
    private function cloneToTarget(
        int $targetId,
        string $mode,
        bool $copyMenu,
        bool $copyPromotions,
        bool $copyDelivery,
        array $sourceMenu,
        ?array $sourceStructure,
        ?array $sourcePolicyFields,
        ?array $sourceDeliverySnapshot,
    ): array {
        $menuStats = ['copied' => 0, 'skipped' => 0];
        $structureStats = ['categories' => 0, 'tags' => 0, 'sets' => 0, 'skipped' => false];
        $promoStats = ['policy' => 'skipped', 'roles_copied' => 0, 'roles_skipped' => 0];
        $deliveryStatus = 'skipped';

        if ($copyMenu) {
            $menuStats = $this->cloneMenu($targetId, $mode, $sourceMenu);
            if ($sourceStructure !== null) {
                $structureStats = $this->cloneStructure($targetId, $mode, $sourceStructure);
            }
        }

        if ($copyPromotions && $sourcePolicyFields !== null) {
            $promoStats = $this->clonePromotions($targetId, $mode, $sourcePolicyFields);
        }

        if ($copyDelivery && $sourceDeliverySnapshot !== null) {
            $deliveryStatus = $this->cloneDelivery($targetId, $mode, $sourceDeliverySnapshot);
        }

        return [
            'branch_id' => $targetId,
            'menu' => $menuStats,
            'structure' => $structureStats,
            'promotions' => $promoStats,
            'delivery' => $deliveryStatus,
        ];
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
     * Порядок важен: теги → сеты (теги сетов через ремап) → категории (ремап id сетов) → теги товаров.
     *
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

    /**
     * @param  array<string, mixed>  $sourcePolicyFields
     * @return array{policy: string, roles_copied: int, roles_skipped: int}
     */
    private function clonePromotions(int $targetId, string $mode, array $sourcePolicyFields): array
    {
        $policyStatus = 'copied';
        if ($mode === self::MODE_SKIP && $this->promotionClone->existsForBranch($targetId)) {
            $policyStatus = 'skipped';
        } else {
            $this->promotionClone->importFlatFields($targetId, $sourcePolicyFields);
        }

        return [
            'policy' => $policyStatus,
            'roles_copied' => 0,
            'roles_skipped' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $sourceDeliverySnapshot
     */
    private function cloneDelivery(int $targetId, string $mode, array $sourceDeliverySnapshot): string
    {
        if ($mode === self::MODE_SKIP && $this->deliveryClone->existsForBranch($targetId)) {
            return 'skipped';
        }

        $this->deliveryClone->import($targetId, $sourceDeliverySnapshot);

        return 'copied';
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
            if ($this->branches->findById($id) === null) {
                throw new \InvalidArgumentException(sprintf('Целевой филиал #%d не найден.', $id));
            }
            $ids[$id] = $id;
        }

        return array_values($ids);
    }
}
