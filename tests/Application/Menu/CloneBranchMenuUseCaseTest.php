<?php

namespace Tests\Application\Menu;

use App\Application\Menu\Command\CloneBranchMenuUseCase;
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
use App\Domain\Supporting\Menu\ValueObject\BranchSetLine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CloneBranchMenuUseCaseTest extends TestCase
{
    public function test_overwrite_menu_from_source_to_target(): void
    {
        $menu = $this->menuRepo([
            1 => [new BranchMenuItem(1, 10, true, 15_000)],
            2 => [new BranchMenuItem(2, 10, false, 9_000)],
        ]);

        $useCase = $this->useCase($menu);
        $result = $useCase->execute(1, [2], 'overwrite');

        $this->assertSame(1, $result[0]['menu']['copied']);
        $this->assertSame(0, $result[0]['menu']['skipped']);
        $saved = $menu->findByBranchAndItemIds(2, [10])[10];
        $this->assertTrue($saved->isAvailable());
        $this->assertSame(15_000, $saved->priceKopecks());
    }

    public function test_skip_existing_menu_keeps_target_price(): void
    {
        $menu = $this->menuRepo([
            1 => [new BranchMenuItem(1, 10, true, 15_000)],
            2 => [new BranchMenuItem(2, 10, false, 9_000)],
        ]);

        $useCase = $this->useCase($menu);
        $result = $useCase->execute(1, [2], 'skip_existing');

        $this->assertSame(0, $result[0]['menu']['copied']);
        $this->assertSame(1, $result[0]['menu']['skipped']);
        $saved = $menu->findByBranchAndItemIds(2, [10])[10];
        $this->assertSame(9_000, $saved->priceKopecks());
    }

    public function test_clone_structure_remaps_set_and_tag_ids(): void
    {
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

        $useCase = $this->useCase($menu, $categories, $tags, $sets);
        $result = $useCase->execute(1, [2], 'overwrite');

        $this->assertSame(
            ['categories' => 1, 'tags' => 1, 'sets' => 1, 'skipped' => false],
            $result[0]['structure'],
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
        $menu = $this->menuRepo([1 => [new BranchMenuItem(1, 10, true, 15_000)]]);

        $categories = $this->createMock(BranchCategoryRepository::class);
        $categories->method('listByBranch')->willReturn([new BranchCategory(9, 2, 'Своя', 'own', 0, true)]);
        $categories->expects($this->never())->method('replaceAllForBranch');

        $tags = $this->createMock(BranchTagRepository::class);
        $tags->expects($this->never())->method('replaceAllForBranch');

        $sets = $this->createMock(BranchSetRepository::class);
        $sets->expects($this->never())->method('replaceAllForBranch');

        $useCase = $this->useCase($menu, $categories, $tags, $sets);
        $result = $useCase->execute(1, [2], 'skip_existing');

        $this->assertTrue($result[0]['structure']['skipped']);
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
        BranchMenuRepository $menu,
        ?BranchCategoryRepository $categories = null,
        ?BranchTagRepository $tags = null,
        ?BranchSetRepository $sets = null,
    ): CloneBranchMenuUseCase {
        $branches = $this->createMock(MenuBranchLookupPort::class);
        $branches->method('assertBranchExists')->willReturnCallback(
            static function (int $branchId): void {
                if ($branchId < 1) {
                    throw new \InvalidArgumentException('Филиал не найден.');
                }
            },
        );

        $defaultCategories = $this->createMock(BranchCategoryRepository::class);
        $defaultCategories->method('listByBranch')->willReturn([]);
        $defaultCategories->method('findItemsByCategoryId')->willReturn([]);

        $defaultTags = $this->createMock(BranchTagRepository::class);
        $defaultTags->method('listByBranch')->willReturn([]);
        $defaultTags->method('findTagIdsForEntries')->willReturn([]);
        $defaultTags->method('replaceAllForBranch')->willReturn([]);

        $defaultSets = $this->createMock(BranchSetRepository::class);
        $defaultSets->method('listByBranch')->willReturn([]);
        $defaultSets->method('replaceAllForBranch')->willReturn([]);

        return new CloneBranchMenuUseCase(
            branches: $branches,
            menu: $menu,
            categories: $categories ?? $defaultCategories,
            tags: $tags ?? $defaultTags,
            sets: $sets ?? $defaultSets,
        );
    }
}
