<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Application\Delivery\Event\DeliveryZonesChangedFactory;
use App\Domain\Core\Delivery\Tariff\Entity\DeliveryConfiguration;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Копирует меню / политики / доставку с одного филиала на один или несколько.
 * Catalog не трогает. Заказы не трогает. Режим overwrite | skip_existing.
 */
final class CloneBranchSettingsUseCase
{
    private const MODE_OVERWRITE = 'overwrite';

    private const MODE_SKIP = 'skip_existing';

    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchMenuRepository $menu,
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
     * @return array{
     *     branch_id: int,
     *     menu: array{copied: int, skipped: int},
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
        ?PromotionPolicy $sourcePolicy,
        ?DeliveryConfiguration $sourceDelivery,
    ): array {
        $menuStats = ['copied' => 0, 'skipped' => 0];
        $promoStats = ['policy' => 'skipped', 'roles_copied' => 0, 'roles_skipped' => 0];
        $deliveryStatus = 'skipped';

        if ($copyMenu) {
            $menuStats = $this->cloneMenu($targetId, $mode, $sourceMenu);
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
     *     gift_product_id: int|null,
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
            'gift_product_id' => $policy->giftProductId(),
            'delivery_free_threshold_kopecks' => max(0, (int) ($delivery['free_delivery_threshold_kopecks'] ?? 0)),
            'delivery_outside_zone_surcharge_kopecks' => max(0, (int) ($delivery['outside_zone_surcharge_kopecks'] ?? 0)),
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
