<?php

namespace App\Application\Company\Command;

use App\Application\Branch\Command\CloneBranchCommercialSettingsUseCase;
use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Application\Menu\Command\CloneBranchMenuUseCase;
use App\Shared\Transaction\TransactionManager;

/**
 * Фасад клонирования настроек филиала: Menu + Branch.
 * Company не владеет меню/тарифами/rules.
 */
final class CloneBranchSettingsUseCase
{
    public function __construct(
        private readonly CloneBranchMenuUseCase $menuClone,
        private readonly CloneBranchCommercialSettingsUseCase $commercialClone,
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
        if (! $dto->copyMenu && ! $dto->copyPromotions && ! $dto->copyDelivery) {
            throw new \InvalidArgumentException('Выбери хотя бы один блок для копирования.');
        }

        $mode = trim($dto->mode) === '' ? 'overwrite' : trim($dto->mode);

        return $this->transaction->run(function () use ($dto, $mode): array {
            /** @var array<int, array{menu: array{copied: int, skipped: int}, structure: array{categories: int, tags: int, sets: int, skipped: bool}}> $menuByBranch */
            $menuByBranch = [];
            if ($dto->copyMenu) {
                foreach ($this->menuClone->execute($dto->sourceBranchId, $dto->targetBranchIds, $mode) as $row) {
                    $menuByBranch[$row['branch_id']] = [
                        'menu' => $row['menu'],
                        'structure' => $row['structure'],
                    ];
                }
            }

            /** @var array<int, array{promotions: array{policy: string, roles_copied: int, roles_skipped: int}, delivery: string}> $commercialByBranch */
            $commercialByBranch = [];
            if ($dto->copyPromotions || $dto->copyDelivery) {
                foreach ($this->commercialClone->execute(
                    sourceBranchId: $dto->sourceBranchId,
                    targetBranchIds: $dto->targetBranchIds,
                    mode: $mode,
                    copyPromotions: $dto->copyPromotions,
                    copyDelivery: $dto->copyDelivery,
                ) as $row) {
                    $commercialByBranch[$row['branch_id']] = [
                        'promotions' => $row['promotions'],
                        'delivery' => $row['delivery'],
                    ];
                }
            }

            $targetIds = array_values(array_unique(array_merge(
                array_keys($menuByBranch),
                array_keys($commercialByBranch),
            )));

            $results = [];
            foreach ($targetIds as $targetId) {
                $menu = $menuByBranch[$targetId] ?? null;
                $commercial = $commercialByBranch[$targetId] ?? null;
                $results[] = [
                    'branch_id' => $targetId,
                    'menu' => $menu['menu'] ?? ['copied' => 0, 'skipped' => 0],
                    'structure' => $menu['structure'] ?? ['categories' => 0, 'tags' => 0, 'sets' => 0, 'skipped' => false],
                    'promotions' => $commercial['promotions'] ?? ['policy' => 'skipped', 'roles_copied' => 0, 'roles_skipped' => 0],
                    'delivery' => $commercial['delivery'] ?? 'skipped',
                ];
            }

            return [
                'source_branch_id' => $dto->sourceBranchId,
                'mode' => $mode,
                'targets' => $results,
            ];
        });
    }
}
