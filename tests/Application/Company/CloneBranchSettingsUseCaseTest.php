<?php

namespace Tests\Application\Company;

use App\Application\Company\Command\CloneBranchSettingsUseCase;
use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Application\Delivery\Event\DeliveryZonesChangedFactory;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Supporting\Company\Entity\Branch;
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
use App\Domain\Supporting\Company\ValueObject\BranchSetLine;
use PHPUnit\Framework\MockObject\MockObject;
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

    public function test_clone_structure_remaps_set_and_tag_ids(): void
    {
        $branches = $this->branchRepo([1, 2]);
        $menu = $this->menuRepo([1 => [new BranchMenuItem(1, 10, true, 15_000)]]);

        $sourceTag = new BranchTag(5, 1, 'hit', 'Хит', 'amber', true, 0);
        $sourceSet = new BranchSet(
            id: 7,
            branchId: 1,
            name: 'Сет',
            slug: 'set',
            description: null,
            isAvailable: true,
            priceKopecks: 50_000,
            status: 'active',
            lines: [new BranchSetLine(10, 2)],
            tagIds: [5],
        );
        $sourceCategory = new BranchCategory(3, 1, 'Роллы', 'rolls', 0, true);

        $categories = $this->createMock(BranchCategoryRepository::class);
        $categories->method('listByBranch')->willReturnCallback(
            static fn (int $branchId): array => $branchId === 1 ? [$sourceCategory] : [],
        );
        $categories->method('findItemsByCategoryId')->with(3)->willReturn([
            new BranchCategoryItem(3, BranchMenuEntryKind::Product, 10, 0),
            new BranchCategoryItem(3, BranchMenuEntryKind::Set, 7, 1),
        ]);
        $savedCategories = null;
        $savedItems = null;
        $categories->expects($this->once())->method('replaceAllForBranch')->willReturnCallback(
            function (int $branchId, array $cats, array $items) use (&$savedCategories, &$savedItems): void {
                $this->assertSame(2, $branchId);
                $savedCategories = $cats;
                $savedItems = $items;
            },
        );

        $tags = $this->createMock(BranchTagRepository::class);
        $tags->method('listByBranch')->willReturnCallback(
            static fn (int $branchId): array => $branchId === 1 ? [$sourceTag] : [],
        );
        $tags->method('findTagIdsForEntries')->willReturn([10 => [5]]);
        $tags->expects($this->once())->method('replaceAllForBranch')->willReturn([5 => 55]);
        $tags->expects($this->once())->method('syncEntryTags')->with(2, BranchMenuEntryKind::Product, 10, [55]);

        $sets = $this->createMock(BranchSetRepository::class);
        $sets->method('listByBranch')->willReturnCallback(
            static fn (int $branchId): array => $branchId === 1 ? [$sourceSet] : [],
        );
        $savedSets = null;
        $sets->expects($this->once())->method('replaceAllForBranch')->willReturnCallback(
            function (int $branchId, array $list) use (&$savedSets): array {
                $this->assertSame(2, $branchId);
                $savedSets = $list;

                return [7 => 77];
            },
        );

        $useCase = $this->useCase($branches, $menu, $categories, $tags, $sets);
        $result = $useCase->execute(new CloneBranchSettingsDto(
            sourceBranchId: 1,
            targetBranchIds: [2],
            copyMenu: true,
            copyPromotions: false,
            copyDelivery: false,
            mode: 'overwrite',
        ));

        $this->assertSame(
            ['categories' => 1, 'tags' => 1, 'sets' => 1, 'skipped' => false],
            $result['targets'][0]['structure'],
        );
        $this->assertCount(1, $savedSets);
        $this->assertSame([55], $savedSets[0]->tagIds());
        $this->assertSame(2, $savedSets[0]->branchId());
        $this->assertCount(1, $savedCategories);
        $this->assertSame(2, $savedCategories[0]->branchId());

        $items = $savedItems[3];
        $this->assertCount(2, $items);
        $this->assertSame(BranchMenuEntryKind::Product, $items[0]->entryKind());
        $this->assertSame(10, $items[0]->entryId());
        $this->assertSame(BranchMenuEntryKind::Set, $items[1]->entryKind());
        $this->assertSame(77, $items[1]->entryId());
    }

    public function test_skip_existing_does_not_touch_structure_when_target_has_it(): void
    {
        $branches = $this->branchRepo([1, 2]);
        $menu = $this->menuRepo([1 => [new BranchMenuItem(1, 10, true, 15_000)]]);

        $categories = $this->createMock(BranchCategoryRepository::class);
        $categories->method('listByBranch')->willReturn([new BranchCategory(9, 2, 'Своя', 'own', 0, true)]);
        $categories->expects($this->never())->method('replaceAllForBranch');

        $tags = $this->createMock(BranchTagRepository::class);
        $tags->expects($this->never())->method('replaceAllForBranch');

        $sets = $this->createMock(BranchSetRepository::class);
        $sets->expects($this->never())->method('replaceAllForBranch');

        $useCase = $this->useCase($branches, $menu, $categories, $tags, $sets);
        $result = $useCase->execute(new CloneBranchSettingsDto(
            sourceBranchId: 1,
            targetBranchIds: [2],
            copyMenu: true,
            copyPromotions: false,
            copyDelivery: false,
            mode: 'skip_existing',
        ));

        $this->assertTrue($result['targets'][0]['structure']['skipped']);
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

    /**
     * @param  (BranchCategoryRepository&MockObject)|null  $categories
     * @param  (BranchTagRepository&MockObject)|null  $tags
     * @param  (BranchSetRepository&MockObject)|null  $sets
     */
    private function useCase(
        BranchRepository $branches,
        BranchMenuRepository $menu,
        ?BranchCategoryRepository $categories = null,
        ?BranchTagRepository $tags = null,
        ?BranchSetRepository $sets = null,
    ): CloneBranchSettingsUseCase {
        $policies = $this->createMock(PromotionPolicyRepository::class);
        $delivery = $this->createMock(DeliveryConfigurationRepository::class);

        return new CloneBranchSettingsUseCase(
            branches: $branches,
            menu: $menu,
            categories: $categories ?? $this->createMock(BranchCategoryRepository::class),
            tags: $tags ?? $this->createMock(BranchTagRepository::class),
            sets: $sets ?? $this->createMock(BranchSetRepository::class),
            policies: $policies,
            delivery: $delivery,
            zonesChanged: new DeliveryZonesChangedFactory(),
            transaction: new ImmediateTransactionManager(),
        );
    }
}
