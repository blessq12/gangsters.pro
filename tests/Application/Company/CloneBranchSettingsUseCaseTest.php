<?php

namespace Tests\Application\Company;

use App\Application\Branch\Command\CloneBranchCommercialSettingsUseCase;
use App\Application\Company\Command\CloneBranchSettingsUseCase;
use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Application\Menu\Command\CloneBranchMenuUseCase;
use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Port\BranchCommercialRulesClonePort;
use App\Domain\Supporting\Branch\Port\BranchDeliveryConfigClonePort;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Domain\Supporting\Menu\Entity\BranchMenuItem;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Menu\Repository\BranchMenuRepository;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;
use App\Domain\Supporting\Menu\Repository\BranchTagRepository;
use PHPUnit\Framework\TestCase;
use Tests\Application\Core\Support\ImmediateTransactionManager;

final class CloneBranchSettingsUseCaseTest extends TestCase
{
    public function test_facade_clones_menu_and_commercial_blocks(): void
    {
        $menu = $this->menuRepo([
            1 => [new BranchMenuItem(1, 10, true, 15_000)],
            2 => [],
        ]);

        $rules = new class implements BranchCommercialRulesClonePort
        {
            /** @var array<int, array<string, mixed>> */
            public array $imported = [];

            public function exportFlatFields(int $branchId): ?array
            {
                return $branchId === 1 ? ['gift_benefit_active' => true] : null;
            }

            public function importFlatFields(int $branchId, array $fields): void
            {
                $this->imported[$branchId] = $fields;
            }

            public function existsForBranch(int $branchId): bool
            {
                return isset($this->imported[$branchId]);
            }
        };

        $delivery = new class implements BranchDeliveryConfigClonePort
        {
            /** @var array<int, array<string, mixed>> */
            public array $imported = [];

            public function export(int $branchId): ?array
            {
                return $branchId === 1
                    ? ['min_order_amount_kopecks' => 1000, 'average_delivery_time_minutes' => 40, 'zones' => []]
                    : null;
            }

            public function import(int $branchId, array $snapshot): void
            {
                $this->imported[$branchId] = $snapshot;
            }

            public function existsForBranch(int $branchId): bool
            {
                return isset($this->imported[$branchId]);
            }
        };

        $useCase = new CloneBranchSettingsUseCase(
            menuClone: $this->menuClone($menu),
            commercialClone: new CloneBranchCommercialSettingsUseCase(
                branches: $this->branchRepo([1, 2]),
                commercialRulesClone: $rules,
                deliveryClone: $delivery,
            ),
            transaction: new ImmediateTransactionManager,
        );

        $result = $useCase->execute(new CloneBranchSettingsDto(
            sourceBranchId: 1,
            targetBranchIds: [2],
            copyMenu: true,
            copyPromotions: true,
            copyDelivery: true,
            mode: 'overwrite',
        ));

        $this->assertSame(1, $result['targets'][0]['menu']['copied']);
        $this->assertSame('copied', $result['targets'][0]['promotions']['policy']);
        $this->assertSame('copied', $result['targets'][0]['delivery']);
        $this->assertArrayHasKey(2, $rules->imported);
        $this->assertArrayHasKey(2, $delivery->imported);
    }

    public function test_facade_skips_unused_commercial_blocks(): void
    {
        $menu = $this->menuRepo([
            1 => [new BranchMenuItem(1, 10, true, 15_000)],
            2 => [],
        ]);

        $rules = new class implements BranchCommercialRulesClonePort
        {
            public function exportFlatFields(int $branchId): ?array
            {
                self::fail('commercial rules не должны вызываться');
            }

            public function importFlatFields(int $branchId, array $fields): void
            {
                self::fail('commercial rules не должны вызываться');
            }

            public function existsForBranch(int $branchId): bool
            {
                self::fail('commercial rules не должны вызываться');
            }
        };

        $delivery = new class implements BranchDeliveryConfigClonePort
        {
            public function export(int $branchId): ?array
            {
                self::fail('delivery clone не должен вызываться');
            }

            public function import(int $branchId, array $snapshot): void
            {
                self::fail('delivery clone не должен вызываться');
            }

            public function existsForBranch(int $branchId): bool
            {
                self::fail('delivery clone не должен вызываться');
            }
        };

        $useCase = new CloneBranchSettingsUseCase(
            menuClone: $this->menuClone($menu),
            commercialClone: new CloneBranchCommercialSettingsUseCase(
                branches: $this->branchRepo([1, 2]),
                commercialRulesClone: $rules,
                deliveryClone: $delivery,
            ),
            transaction: new ImmediateTransactionManager,
        );

        $result = $useCase->execute(new CloneBranchSettingsDto(
            sourceBranchId: 1,
            targetBranchIds: [2],
            copyMenu: true,
            copyPromotions: false,
            copyDelivery: false,
            mode: 'overwrite',
        ));

        $this->assertSame(1, $result['targets'][0]['menu']['copied']);
        $this->assertSame('skipped', $result['targets'][0]['delivery']);
        $this->assertSame('skipped', $result['targets'][0]['promotions']['policy']);
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

    private function menuClone(BranchMenuRepository $menu): CloneBranchMenuUseCase
    {
        $branches = $this->createMock(MenuBranchLookupPort::class);
        $branches->method('assertBranchExists');

        $categories = $this->createMock(BranchCategoryRepository::class);
        $categories->method('listByBranch')->willReturn([]);
        $categories->method('findItemsByCategoryId')->willReturn([]);

        $tags = $this->createMock(BranchTagRepository::class);
        $tags->method('listByBranch')->willReturn([]);
        $tags->method('findTagIdsForEntries')->willReturn([]);
        $tags->method('replaceAllForBranch')->willReturn([]);

        $sets = $this->createMock(BranchSetRepository::class);
        $sets->method('listByBranch')->willReturn([]);
        $sets->method('replaceAllForBranch')->willReturn([]);

        return new CloneBranchMenuUseCase(
            branches: $branches,
            menu: $menu,
            categories: $categories,
            tags: $tags,
            sets: $sets,
        );
    }
}
