<?php

namespace Tests\Application\Company;

use App\Application\Company\Command\CloneBranchSettingsUseCase;
use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Application\Delivery\Event\DeliveryZonesChangedFactory;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use PHPUnit\Framework\TestCase;
use Tests\Application\Core\Support\ImmediateTransactionManager;

final class CloneBranchSettingsUseCaseTest extends TestCase
{
    public function test_overwrite_menu_from_source_to_target(): void
    {
        $branches = $this->branchRepo([1, 2]);
        $menu = $this->menuRepo([
            1 => [new BranchMenuItem(1, 10, true, 15_000)],
            2 => [new BranchMenuItem(2, 10, false, 9_000)],
        ]);

        $useCase = $this->useCase($branches, $menu);
        $result = $useCase->execute(new CloneBranchSettingsDto(
            sourceBranchId: 1,
            targetBranchIds: [2],
            copyMenu: true,
            copyPromotions: false,
            copyDelivery: false,
            mode: 'overwrite',
        ));

        $this->assertSame(1, $result['targets'][0]['menu']['copied']);
        $this->assertSame(0, $result['targets'][0]['menu']['skipped']);
        $saved = $menu->findByBranchAndItemIds(2, [10])[10];
        $this->assertTrue($saved->isAvailable());
        $this->assertSame(15_000, $saved->priceKopecks());
    }

    public function test_skip_existing_menu_keeps_target_price(): void
    {
        $branches = $this->branchRepo([1, 2]);
        $menu = $this->menuRepo([
            1 => [new BranchMenuItem(1, 10, true, 15_000)],
            2 => [new BranchMenuItem(2, 10, false, 9_000)],
        ]);

        $useCase = $this->useCase($branches, $menu);
        $result = $useCase->execute(new CloneBranchSettingsDto(
            sourceBranchId: 1,
            targetBranchIds: [2],
            copyMenu: true,
            copyPromotions: false,
            copyDelivery: false,
            mode: 'skip_existing',
        ));

        $this->assertSame(0, $result['targets'][0]['menu']['copied']);
        $this->assertSame(1, $result['targets'][0]['menu']['skipped']);
        $saved = $menu->findByBranchAndItemIds(2, [10])[10];
        $this->assertSame(9_000, $saved->priceKopecks());
    }

    /**
     * @param  list<int>  $ids
     */
    private function branchRepo(array $ids): BranchRepository
    {
        $map = [];
        foreach ($ids as $id) {
            $map[$id] = new Branch(
                id: $id,
                companyId: 1,
                name: 'Филиал '.$id,
                isActive: true,
                schedule: [],
            );
        }

        return new class($map) implements BranchRepository
        {
            /** @param  array<int, Branch>  $map */
            public function __construct(private array $map) {}

            public function findById(int $id): ?Branch
            {
                return $this->map[$id] ?? null;
            }

            public function listByCompany(int $companyId): array
            {
                return array_values($this->map);
            }

            public function listPageByCompany(int $companyId, int $offset, int $limit): array
            {
                return array_slice($this->listByCompany($companyId), $offset, $limit);
            }

            public function countByCompany(int $companyId): int
            {
                return count($this->map);
            }

            public function save(Branch $branch): Branch
            {
                $this->map[$branch->id()] = $branch;

                return $branch;
            }
        };
    }

    /**
     * @param  array<int, list<BranchMenuItem>>  $byBranch
     */
    private function menuRepo(array $byBranch): BranchMenuRepository
    {
        return new class($byBranch) implements BranchMenuRepository
        {
            /** @param  array<int, list<BranchMenuItem>>  $byBranch */
            public function __construct(private array $byBranch) {}

            public function listByBranch(int $branchId): array
            {
                return $this->byBranch[$branchId] ?? [];
            }

            public function listAvailableByBranch(int $branchId): array
            {
                return array_values(array_filter(
                    $this->listByBranch($branchId),
                    static fn (BranchMenuItem $item): bool => $item->isAvailable(),
                ));
            }

            public function findByBranchAndItemIds(int $branchId, array $catalogItemIds): array
            {
                $out = [];
                foreach ($this->listByBranch($branchId) as $item) {
                    if (in_array($item->catalogItemId(), $catalogItemIds, true)) {
                        $out[$item->catalogItemId()] = $item;
                    }
                }

                return $out;
            }

            public function upsert(BranchMenuItem $item): BranchMenuItem
            {
                $list = $this->byBranch[$item->branchId()] ?? [];
                $replaced = false;
                foreach ($list as $index => $existing) {
                    if ($existing->catalogItemId() === $item->catalogItemId()) {
                        $list[$index] = $item;
                        $replaced = true;
                        break;
                    }
                }
                if (! $replaced) {
                    $list[] = $item;
                }
                $this->byBranch[$item->branchId()] = $list;

                return $item;
            }

            public function upsertMany(int $branchId, array $items): void
            {
                foreach ($items as $item) {
                    $this->upsert($item);
                }
            }

            public function delete(int $branchId, int $catalogItemId): void
            {
                $this->byBranch[$branchId] = array_values(array_filter(
                    $this->listByBranch($branchId),
                    static fn (BranchMenuItem $item): bool => $item->catalogItemId() !== $catalogItemId,
                ));
            }
        };
    }

    private function useCase(BranchRepository $branches, BranchMenuRepository $menu): CloneBranchSettingsUseCase
    {
        $policies = $this->createMock(PromotionPolicyRepository::class);
        $delivery = $this->createMock(DeliveryConfigurationRepository::class);

        return new CloneBranchSettingsUseCase(
            branches: $branches,
            menu: $menu,
            policies: $policies,
            delivery: $delivery,
            zonesChanged: new DeliveryZonesChangedFactory(),
            transaction: new ImmediateTransactionManager(),
        );
    }
}
