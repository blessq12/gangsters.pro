<?php

namespace App\Application\Branch\Command;

use App\Domain\Supporting\Branch\Port\BranchCommercialRulesClonePort;
use App\Domain\Supporting\Branch\Port\BranchDeliveryConfigClonePort;
use App\Domain\Supporting\Branch\Repository\BranchRepository;

/**
 * Клонирование commercial rules и тарифной конфигурации между филиалами.
 */
final class CloneBranchCommercialSettingsUseCase
{
    private const MODE_OVERWRITE = 'overwrite';

    private const MODE_SKIP = 'skip_existing';

    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchCommercialRulesClonePort $commercialRulesClone,
        private readonly BranchDeliveryConfigClonePort $deliveryClone,
    ) {}

    /**
     * @param  list<int>  $targetBranchIds
     * @return list<array{
     *     branch_id: int,
     *     promotions: array{policy: string, roles_copied: int, roles_skipped: int},
     *     delivery: string
     * }>
     */
    public function execute(
        int $sourceBranchId,
        array $targetBranchIds,
        string $mode,
        bool $copyPromotions,
        bool $copyDelivery,
    ): array {
        $mode = $this->normalizeMode($mode);
        if ($sourceBranchId < 1) {
            throw new \InvalidArgumentException('Некорректный source_branch_id.');
        }
        if ($this->branches->findById($sourceBranchId) === null) {
            throw new \InvalidArgumentException('Филиал-источник не найден.');
        }
        if (! $copyPromotions && ! $copyDelivery) {
            throw new \InvalidArgumentException('Выбери хотя бы один блок для копирования.');
        }

        $targets = $this->normalizeTargets($sourceBranchId, $targetBranchIds);

        $sourceCommercialRulesFields = $copyPromotions
            ? $this->commercialRulesClone->exportFlatFields($sourceBranchId)
            : null;
        $sourceDeliverySnapshot = $copyDelivery
            ? $this->deliveryClone->export($sourceBranchId)
            : null;

        if ($copyPromotions && $sourceCommercialRulesFields === null) {
            throw new \InvalidArgumentException('У филиала-источника нет политики акций.');
        }
        if ($copyDelivery && $sourceDeliverySnapshot === null) {
            throw new \InvalidArgumentException('У филиала-источника нет настроек доставки.');
        }

        $results = [];
        foreach ($targets as $targetId) {
            $promoStats = ['policy' => 'skipped', 'roles_copied' => 0, 'roles_skipped' => 0];
            $deliveryStatus = 'skipped';

            if ($copyPromotions && $sourceCommercialRulesFields !== null) {
                $promoStats = $this->clonePromotions($targetId, $mode, $sourceCommercialRulesFields);
            }
            if ($copyDelivery && $sourceDeliverySnapshot !== null) {
                $deliveryStatus = $this->cloneDelivery($targetId, $mode, $sourceDeliverySnapshot);
            }

            $results[] = [
                'branch_id' => $targetId,
                'promotions' => $promoStats,
                'delivery' => $deliveryStatus,
            ];
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $sourceCommercialRulesFields
     * @return array{policy: string, roles_copied: int, roles_skipped: int}
     */
    private function clonePromotions(int $targetId, string $mode, array $sourceCommercialRulesFields): array
    {
        $policyStatus = 'copied';
        if ($mode === self::MODE_SKIP && $this->commercialRulesClone->existsForBranch($targetId)) {
            $policyStatus = 'skipped';
        } else {
            $this->commercialRulesClone->importFlatFields($targetId, $sourceCommercialRulesFields);
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

        if ($ids === []) {
            throw new \InvalidArgumentException('Нужен хотя бы один целевой филиал.');
        }

        return array_values($ids);
    }
}
