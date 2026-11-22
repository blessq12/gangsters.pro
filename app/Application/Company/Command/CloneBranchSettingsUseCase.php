<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Application\Delivery\Event\DeliveryZonesChangedFactory;
use App\Domain\Core\Delivery\Tariff\Entity\DeliveryConfiguration;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Supporting\Company\Entity\BranchCategory;
use App\Domain\Supporting\Company\Entity\BranchCategoryItem;
use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Domain\Supporting\Company\Entity\BranchSet;
use App\Domain\Supporting\Company\Entity\BranchTag;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
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
        private readonly PromotionPolicyRepository $policies,
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly DeliveryZonesChangedFactory $zonesChanged,
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
            $sourcePolicy = $dto->copyPromotions ? $this->policies->findByBranch($sourceId) : null;
            $sourceDelivery = $dto->copyDelivery ? $this->delivery->findByBranch($sourceId) : null;

            if ($dto->copyPromotions && $sourcePolicy === null) {
                throw new \InvalidArgumentException('У филиала-источника нет политики акций.');
            }
            if ($dto->copyDelivery && $sourceDelivery === null) {
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
                    sourcePolicy: $sourcePolicy,
                    sourceDelivery: $sourceDelivery,
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
        ?PromotionPolicy $sourcePolicy,
        ?DeliveryConfiguration $sourceDelivery,
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

        if ($copyPromotions && $sourcePolicy instanceof PromotionPolicy) {
            $promoStats = $this->clonePromotions($targetId, $mode, $sourcePolicy);
        }

        if ($copyDelivery && $sourceDelivery instanceof DeliveryConfiguration) {
            $deliveryStatus = $this->cloneDelivery($targetId, $mode, $sourceDelivery);
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
                isGiftCandidate: $item->isGiftCandidate(),
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
     * @return array{policy: string, roles_copied: int, roles_skipped: int}
     */
    private function clonePromotions(
        int $targetId,
        string $mode,
        PromotionPolicy $sourcePolicy,
    ): array {
        $policyStatus = 'copied';
        $targetPolicy = $this->policies->findByBranch($targetId);
        if ($mode === self::MODE_SKIP && $targetPolicy !== null) {
            $policyStatus = 'skipped';
        } else {
            $this->policies->saveForBranch($targetId, $this->policyToFlatFields($sourcePolicy));
        }

        return [
            'policy' => $policyStatus,
            'roles_copied' => 0,
            'roles_skipped' => 0,
        ];
    }

    private function cloneDelivery(int $targetId, string $mode, DeliveryConfiguration $source): string
    {
        $target = $this->delivery->findByBranch($targetId);
        if ($mode === self::MODE_SKIP && $target !== null) {
            return 'skipped';
        }

        $zones = [];
        foreach ($source->zones() as $zone) {
            $zones[] = [
                'id' => $zone->id(),
                'name' => $zone->name(),
                'fee_rubles' => (int) round($zone->deliveryFeeKopecks() / 100),
                'is_remote' => $zone->isRemote(),
                'geometry' => $zone->geometry(),
            ];
        }

        $before = $this->zonesChanged->snapshot($target);
        $after = $this->delivery->saveSettingsForBranch(
            branchId: $targetId,
            minOrderAmountKopecks: $source->minOrderAmountKopecks(),
            averageDeliveryTimeMinutes: $source->averageDeliveryTimeMinutes(),
            zones: $zones,
        );

        $event = $this->zonesChanged->createIfChanged($before, $after);
        if ($event !== null) {
            event($event);
        }

        return 'copied';
    }

    /**
     * @return array{
     *     gift_pickup_min_order_kopecks: int,
     *     gift_courier_min_order_kopecks: int,
     *     gift_courier_weekdays: list<int>,
     *     gift_benefit_active: bool,
     *     delivery_free_threshold_kopecks: int,
     *     delivery_outside_zone_surcharge_kopecks: int,
     *     delivery_benefit_active: bool,
     *     complement_set_benefit_active: bool,
     *     complement_set_rolls_per_set: int,
     *     complement_set_max_sets: int
     * }
     */
    private function policyToFlatFields(PromotionPolicy $policy): array
    {
        $pickup = $policy->giftRuleForChannel('pickup') ?? [];
        $courier = $policy->giftRuleForChannel('courier') ?? [];
        $delivery = $policy->deliveryBenefit();
        $complement = $policy->complementSetBenefit();

        $weekdays = [];
        foreach ($courier['allowed_weekdays'] ?? [] as $day) {
            $int = (int) $day;
            if ($int >= 1 && $int <= 7) {
                $weekdays[$int] = $int;
            }
        }
        $weekdays = array_values($weekdays);
        sort($weekdays);

        return [
            'gift_pickup_min_order_kopecks' => max(1, (int) ($pickup['min_order_amount_kopecks'] ?? 1)),
            'gift_courier_min_order_kopecks' => max(1, (int) ($courier['min_order_amount_kopecks'] ?? 1)),
            'gift_courier_weekdays' => $weekdays,
            'gift_benefit_active' => (bool) (($pickup['is_active'] ?? false) || ($courier['is_active'] ?? false)),
            'delivery_free_threshold_kopecks' => max(0, (int) ($delivery['free_delivery_threshold_kopecks'] ?? 0)),
            'delivery_outside_zone_surcharge_kopecks' => 0,
            'delivery_benefit_active' => (bool) ($delivery['is_active'] ?? false),
            'complement_set_benefit_active' => (bool) ($complement['is_active'] ?? false),
            'complement_set_rolls_per_set' => max(1, (int) ($complement['rolls_per_set'] ?? 1)),
            'complement_set_max_sets' => max(1, (int) ($complement['max_sets'] ?? 1)),
        ];
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
