<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\BranchSet;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;
use App\Domain\Supporting\Company\Repository\BranchTagRepository;
use App\Domain\Supporting\Company\ValueObject\BranchSetLine;
use App\Infrastructure\Company\Mapper\BranchSetMapper;
use App\Infrastructure\Company\Model\BranchCategoryItemModel;
use App\Infrastructure\Company\Model\BranchSetLineModel;
use App\Infrastructure\Company\Model\BranchSetModel;
use App\Infrastructure\Company\Support\BranchSlugGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class EloquentBranchSetRepository implements BranchSetRepository
{
    public function __construct(
        private readonly BranchSetMapper $mapper,
        private readonly BranchTagRepository $tags,
        private readonly BranchSlugGenerator $slugs,
    ) {}

    public function listByBranch(int $branchId): array
    {
        $rows = BranchSetModel::query()
            ->with('lines')
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return $this->hydrate($rows);
    }

    public function findAvailableByBranchAndIds(int $branchId, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($id) => (int) $id, $ids),
            static fn (int $id): bool => $id > 0,
        )));
        if ($ids === []) {
            return [];
        }

        $rows = BranchSetModel::query()
            ->with('lines')
            ->where('branch_id', $branchId)
            ->where('is_available', true)
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return $this->hydrate($rows);
    }

    public function findById(int $branchId, int $id): ?BranchSet
    {
        $row = BranchSetModel::query()
            ->with('lines')
            ->where('branch_id', $branchId)
            ->where('id', $id)
            ->first();
        if (! $row instanceof BranchSetModel) {
            return null;
        }

        return $this->mapper->toDomain(
            $row,
            $this->tags->findTagIdsForEntry(BranchMenuEntryKind::Set, (int) $row->id),
        );
    }

    public function save(
        int $branchId,
        ?int $id,
        string $name,
        string $slug,
        ?string $description,
        bool $isAvailable,
        int $priceKopecks,
        string $status,
        array $lines,
        array $tagIds = [],
    ): BranchSet {
        if ($priceKopecks < 0) {
            throw new \InvalidArgumentException('Цена сета не может быть отрицательной.');
        }
        $normalizedLines = $this->normalizeLines($lines);

        $setId = DB::transaction(function () use (
            $branchId,
            $id,
            $name,
            $slug,
            $description,
            $isAvailable,
            $priceKopecks,
            $status,
            $normalizedLines,
            $tagIds,
        ): int {
            $scope = BranchSetModel::query()->where('branch_id', $branchId);
            $slug = trim($slug);

            if ($id === null) {
                $row = new BranchSetModel;
                $row->branch_id = $branchId;
                if ($slug === '') {
                    $slug = $this->slugs->generate($scope, $name, $branchId, 'set');
                } elseif ($this->slugs->isTaken($scope, $slug)) {
                    throw new \InvalidArgumentException('Такой slug сета уже есть в филиале.');
                }
            } else {
                $row = BranchSetModel::query()
                    ->where('branch_id', $branchId)
                    ->where('id', $id)
                    ->first();
                if (! $row instanceof BranchSetModel) {
                    throw new \InvalidArgumentException('Сет не найден.');
                }
                if ($slug === '') {
                    $slug = (string) $row->slug;
                } elseif ($this->slugs->isTaken($scope, $slug, $id)) {
                    throw new \InvalidArgumentException('Такой slug сета уже есть в филиале.');
                }
            }

            $row->fill([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'is_available' => $isAvailable,
                'price_kopecks' => $priceKopecks,
                'status' => $status,
            ]);
            $row->save();

            $this->writeLines((int) $row->id, $normalizedLines);
            $this->tags->syncEntryTags($branchId, BranchMenuEntryKind::Set, (int) $row->id, $tagIds);

            return (int) $row->id;
        });

        $saved = $this->findById($branchId, $setId);
        if ($saved === null) {
            throw new \RuntimeException('Не удалось прочитать сохранённый сет.');
        }

        return $saved;
    }

    public function delete(int $branchId, int $id): void
    {
        DB::transaction(function () use ($branchId, $id): void {
            $row = BranchSetModel::query()
                ->where('branch_id', $branchId)
                ->where('id', $id)
                ->first();
            if (! $row instanceof BranchSetModel) {
                return;
            }

            $this->wipeSets($branchId, [(int) $row->id]);
        });
    }

    /**
     * Теги сета переносятся по tagIds сущности (теги должны уже существовать в филиале).
     * Позиции категорий и теги, ссылавшиеся на старые сеты, удаляются вместе с ними.
     *
     * @return array<int, int> oldId => newId
     */
    public function replaceAllForBranch(int $branchId, array $sets): array
    {
        return DB::transaction(function () use ($branchId, $sets): array {
            $existingIds = BranchSetModel::query()
                ->where('branch_id', $branchId)
                ->pluck('id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all();
            $this->wipeSets($branchId, $existingIds);

            $map = [];
            foreach ($sets as $set) {
                if (! $set instanceof BranchSet) {
                    continue;
                }

                $row = BranchSetModel::query()->create([
                    'branch_id' => $branchId,
                    'name' => $set->name(),
                    'slug' => $set->slug(),
                    'description' => $set->description(),
                    'is_available' => $set->isAvailable(),
                    'price_kopecks' => $set->priceKopecks(),
                    'status' => $set->status(),
                ]);

                $lines = [];
                foreach ($set->lines() as $line) {
                    $lines[] = [
                        'catalog_item_id' => $line->catalogItemId(),
                        'quantity' => $line->quantity(),
                    ];
                }
                $this->writeLines((int) $row->id, $this->normalizeLines($lines));

                if ($set->tagIds() !== []) {
                    $this->tags->syncEntryTags($branchId, BranchMenuEntryKind::Set, (int) $row->id, $set->tagIds());
                }

                $map[$set->id()] = (int) $row->id;
            }

            return $map;
        });
    }

    /**
     * @param  Collection<int, BranchSetModel>  $rows
     * @return list<BranchSet>
     */
    private function hydrate(Collection $rows): array
    {
        $ids = $rows->map(static fn (BranchSetModel $row): int => (int) $row->id)->all();
        $tagIdsBySet = $this->tags->findTagIdsForEntries(BranchMenuEntryKind::Set, $ids);

        return $rows
            ->map(fn (BranchSetModel $row) => $this->mapper->toDomain($row, $tagIdsBySet[(int) $row->id] ?? []))
            ->values()
            ->all();
    }

    /**
     * @param  list<array{catalog_item_id: int, quantity: int}>  $lines
     * @return list<BranchSetLine> одинаковые catalog_item_id объединяются (quantity суммируется)
     */
    private function normalizeLines(array $lines): array
    {
        $quantities = [];
        foreach ($lines as $line) {
            $catalogItemId = (int) ($line['catalog_item_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            // VO валидирует значения
            $valid = new BranchSetLine($catalogItemId, $quantity);
            $quantities[$valid->catalogItemId()] = ($quantities[$valid->catalogItemId()] ?? 0) + $valid->quantity();
        }

        $out = [];
        foreach ($quantities as $catalogItemId => $quantity) {
            $out[] = new BranchSetLine($catalogItemId, $quantity);
        }

        return $out;
    }

    /**
     * @param  list<BranchSetLine>  $lines
     */
    private function writeLines(int $setId, array $lines): void
    {
        BranchSetLineModel::query()->where('set_id', $setId)->delete();

        foreach ($lines as $line) {
            BranchSetLineModel::query()->create([
                'set_id' => $setId,
                'catalog_item_id' => $line->catalogItemId(),
                'quantity' => $line->quantity(),
            ]);
        }
    }

    /**
     * Удаляет сеты вместе со строками, тегами и позициями категорий (полиморфные ссылки без FK).
     *
     * @param  list<int>  $setIds
     */
    private function wipeSets(int $branchId, array $setIds): void
    {
        if ($setIds === []) {
            return;
        }

        foreach ($setIds as $setId) {
            $this->tags->syncEntryTags($branchId, BranchMenuEntryKind::Set, $setId, []);
        }

        BranchCategoryItemModel::query()
            ->where('entry_kind', BranchMenuEntryKind::Set->value)
            ->whereIn('entry_id', $setIds)
            ->delete();
        BranchSetLineModel::query()->whereIn('set_id', $setIds)->delete();
        BranchSetModel::query()->where('branch_id', $branchId)->whereIn('id', $setIds)->delete();
    }
}
