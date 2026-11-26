<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\BranchCategory;
use App\Domain\Supporting\Company\Entity\BranchCategoryItem;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Infrastructure\Company\Mapper\BranchCategoryMapper;
use App\Infrastructure\Company\Model\BranchCategoryItemModel;
use App\Infrastructure\Company\Model\BranchCategoryModel;
use App\Infrastructure\Company\Support\BranchSlugGenerator;
use Illuminate\Support\Facades\DB;

final class EloquentBranchCategoryRepository implements BranchCategoryRepository
{
    public function __construct(
        private readonly BranchCategoryMapper $mapper,
        private readonly BranchSlugGenerator $slugs,
    ) {}

    public function listByBranch(int $branchId): array
    {
        return BranchCategoryModel::query()
            ->where('branch_id', $branchId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (BranchCategoryModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listActiveOrderedByBranch(int $branchId): array
    {
        return BranchCategoryModel::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (BranchCategoryModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findById(int $branchId, int $id): ?BranchCategory
    {
        $row = BranchCategoryModel::query()
            ->where('branch_id', $branchId)
            ->where('id', $id)
            ->first();

        return $row instanceof BranchCategoryModel ? $this->mapper->toDomain($row) : null;
    }

    public function findItemsByCategoryId(int $categoryId): array
    {
        return BranchCategoryItemModel::query()
            ->where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (BranchCategoryItemModel $row) => $this->mapper->itemToDomain($row))
            ->all();
    }

    public function save(
        int $branchId,
        ?int $id,
        string $name,
        string $slug,
        int $sortOrder,
        bool $isActive,
        bool $isAccompanying,
        bool $isComplement = false,
        bool $isGift = false,
        ?array $items = null,
    ): BranchCategory {
        BranchCategory::assertExclusiveRoles($isAccompanying, $isComplement, $isGift);
        $normalizedItems = $items === null ? null : $this->normalizeItems($items);

        return DB::transaction(function () use (
            $branchId,
            $id,
            $name,
            $slug,
            $sortOrder,
            $isActive,
            $isAccompanying,
            $isComplement,
            $isGift,
            $normalizedItems,
        ): BranchCategory {
            $scope = BranchCategoryModel::query()->where('branch_id', $branchId);
            $slug = trim($slug);

            if ($id === null) {
                $row = new BranchCategoryModel;
                $row->branch_id = $branchId;
                if ($slug === '') {
                    $slug = $this->slugs->generate($scope, $name, $branchId, 'category');
                } elseif ($this->slugs->isTaken($scope, $slug)) {
                    throw new \InvalidArgumentException('Такой slug категории уже есть в филиале.');
                }
            } else {
                $row = BranchCategoryModel::query()
                    ->where('branch_id', $branchId)
                    ->where('id', $id)
                    ->first();
                if (! $row instanceof BranchCategoryModel) {
                    throw new \InvalidArgumentException('Категория не найдена.');
                }
                if ($slug === '') {
                    $slug = (string) $row->slug;
                } elseif ($this->slugs->isTaken($scope, $slug, $id)) {
                    throw new \InvalidArgumentException('Такой slug категории уже есть в филиале.');
                }
            }

            $row->fill([
                'name' => $name,
                'slug' => $slug,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
                'is_accompanying' => $isAccompanying,
                'is_complement' => $isComplement,
                'is_gift' => $isGift,
            ]);
            $row->save();

            if ($normalizedItems !== null) {
                $this->writeItems((int) $row->id, $normalizedItems);
            }

            return $this->mapper->toDomain($row);
        });
    }

    public function reorder(int $branchId, array $ordered): void
    {
        DB::transaction(function () use ($branchId, $ordered): void {
            foreach ($ordered as $entry) {
                BranchCategoryModel::query()
                    ->where('branch_id', $branchId)
                    ->where('id', (int) $entry['id'])
                    ->update(['sort_order' => (int) $entry['sort_order']]);
            }
        });
    }

    public function replaceItems(int $branchId, int $categoryId, array $items): void
    {
        $normalized = $this->normalizeItems($items);

        DB::transaction(function () use ($branchId, $categoryId, $normalized): void {
            $exists = BranchCategoryModel::query()
                ->where('branch_id', $branchId)
                ->where('id', $categoryId)
                ->exists();
            if (! $exists) {
                throw new \InvalidArgumentException('Категория не найдена.');
            }

            $this->writeItems($categoryId, $normalized);
        });
    }

    public function delete(int $branchId, int $id): void
    {
        DB::transaction(function () use ($branchId, $id): void {
            $row = BranchCategoryModel::query()
                ->where('branch_id', $branchId)
                ->where('id', $id)
                ->first();
            if (! $row instanceof BranchCategoryModel) {
                return;
            }

            BranchCategoryItemModel::query()->where('category_id', $row->id)->delete();
            $row->delete();
        });
    }

    public function replaceAllForBranch(int $branchId, array $categories, array $itemsByCategoryId): void
    {
        DB::transaction(function () use ($branchId, $categories, $itemsByCategoryId): void {
            $existingIds = BranchCategoryModel::query()
                ->where('branch_id', $branchId)
                ->pluck('id')
                ->all();
            if ($existingIds !== []) {
                BranchCategoryItemModel::query()->whereIn('category_id', $existingIds)->delete();
                BranchCategoryModel::query()->whereIn('id', $existingIds)->delete();
            }

            foreach ($categories as $category) {
                if (! $category instanceof BranchCategory) {
                    continue;
                }

                $row = BranchCategoryModel::query()->create([
                    'branch_id' => $branchId,
                    'name' => $category->name(),
                    'slug' => $category->slug(),
                    'sort_order' => $category->sortOrder(),
                    'is_active' => $category->isActive(),
                    'is_accompanying' => $category->isAccompanying(),
                    'is_complement' => $category->isComplement(),
                    'is_gift' => $category->isGift(),
                ]);

                $rawItems = [];
                foreach ($itemsByCategoryId[$category->id()] ?? [] as $item) {
                    if (! $item instanceof BranchCategoryItem) {
                        continue;
                    }
                    $rawItems[] = [
                        'entry_kind' => $item->entryKind()->value,
                        'entry_id' => $item->entryId(),
                        'sort_order' => $item->sortOrder(),
                    ];
                }

                $this->writeItems((int) $row->id, $this->normalizeItems($rawItems));
            }
        });
    }

    /**
     * @param  list<array{entry_kind: string, entry_id: int, sort_order: int}>  $items
     * @return list<array{entry_kind: BranchMenuEntryKind, entry_id: int, sort_order: int}>
     */
    private function normalizeItems(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            $kind = BranchMenuEntryKind::tryFrom((string) ($item['entry_kind'] ?? ''));
            if ($kind === null) {
                throw new \InvalidArgumentException('Некорректный entry_kind позиции категории.');
            }
            $entryId = (int) ($item['entry_id'] ?? 0);
            if ($entryId < 1) {
                throw new \InvalidArgumentException('entry_id позиции категории должен быть положительным.');
            }

            $key = $kind->value.':'.$entryId;
            if (isset($out[$key])) {
                continue;
            }
            $out[$key] = [
                'entry_kind' => $kind,
                'entry_id' => $entryId,
                'sort_order' => (int) ($item['sort_order'] ?? 0),
            ];
        }

        return array_values($out);
    }

    /**
     * @param  list<array{entry_kind: BranchMenuEntryKind, entry_id: int, sort_order: int}>  $items
     */
    private function writeItems(int $categoryId, array $items): void
    {
        BranchCategoryItemModel::query()->where('category_id', $categoryId)->delete();

        foreach ($items as $item) {
            BranchCategoryItemModel::query()->create([
                'category_id' => $categoryId,
                'entry_kind' => $item['entry_kind'],
                'entry_id' => $item['entry_id'],
                'sort_order' => $item['sort_order'],
            ]);
        }
    }
}
