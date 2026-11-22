<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\BranchTag;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Company\Repository\BranchTagRepository;
use App\Infrastructure\Company\Mapper\BranchTagMapper;
use App\Infrastructure\Company\Model\CMP_BranchEntryTag;
use App\Infrastructure\Company\Model\CMP_BranchTag;
use Illuminate\Support\Facades\DB;

final class EloquentBranchTagRepository implements BranchTagRepository
{
    public function __construct(
        private readonly BranchTagMapper $mapper,
    ) {}

    public function listByBranch(int $branchId): array
    {
        return CMP_BranchTag::query()
            ->where('branch_id', $branchId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CMP_BranchTag $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listActiveOrderedByBranch(int $branchId): array
    {
        return CMP_BranchTag::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CMP_BranchTag $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findById(int $branchId, int $id): ?BranchTag
    {
        $row = CMP_BranchTag::query()
            ->where('branch_id', $branchId)
            ->where('id', $id)
            ->first();

        return $row instanceof CMP_BranchTag ? $this->mapper->toDomain($row) : null;
    }

    public function save(
        int $branchId,
        ?int $id,
        string $code,
        string $label,
        string $color,
        bool $isActive,
        int $sortOrder,
    ): BranchTag {
        $code = trim($code);

        $exists = CMP_BranchTag::query()
            ->where('branch_id', $branchId)
            ->where('code', $code)
            ->when($id !== null, fn ($query) => $query->where('id', '!=', $id))
            ->exists();
        if ($exists) {
            throw new \InvalidArgumentException('Такой код тега уже есть в филиале.');
        }

        if ($id === null) {
            $row = new CMP_BranchTag();
            $row->branch_id = $branchId;
        } else {
            $row = CMP_BranchTag::query()
                ->where('branch_id', $branchId)
                ->where('id', $id)
                ->first();
            if (! $row instanceof CMP_BranchTag) {
                throw new \InvalidArgumentException('Тег не найден.');
            }
        }

        $row->fill([
            'code' => $code,
            'label' => $label,
            'color' => $color,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }

    public function delete(int $branchId, int $id): void
    {
        DB::transaction(function () use ($branchId, $id): void {
            $row = CMP_BranchTag::query()
                ->where('branch_id', $branchId)
                ->where('id', $id)
                ->first();
            if (! $row instanceof CMP_BranchTag) {
                return;
            }

            CMP_BranchEntryTag::query()->where('tag_id', $row->id)->delete();
            $row->delete();
        });
    }

    public function syncEntryTags(int $branchId, BranchMenuEntryKind $kind, int $entryId, array $tagIds): void
    {
        $wanted = $this->normalizeIds($tagIds);

        DB::transaction(function () use ($branchId, $kind, $entryId, $wanted): void {
            $branchTagIds = CMP_BranchTag::query()
                ->where('branch_id', $branchId)
                ->pluck('id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all();

            if ($branchTagIds !== []) {
                CMP_BranchEntryTag::query()
                    ->where('entry_kind', $kind->value)
                    ->where('entry_id', $entryId)
                    ->whereIn('tag_id', $branchTagIds)
                    ->delete();
            }

            $allowed = array_values(array_intersect($wanted, $branchTagIds));
            foreach ($allowed as $tagId) {
                CMP_BranchEntryTag::query()->create([
                    'tag_id' => $tagId,
                    'entry_kind' => $kind,
                    'entry_id' => $entryId,
                ]);
            }
        });
    }

    public function findTagIdsForEntry(BranchMenuEntryKind $kind, int $entryId): array
    {
        return CMP_BranchEntryTag::query()
            ->where('entry_kind', $kind->value)
            ->where('entry_id', $entryId)
            ->orderBy('tag_id')
            ->pluck('tag_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    public function findTagIdsForEntries(BranchMenuEntryKind $kind, array $entryIds): array
    {
        $ids = $this->normalizeIds($entryIds);
        if ($ids === []) {
            return [];
        }

        $out = [];
        $rows = CMP_BranchEntryTag::query()
            ->where('entry_kind', $kind->value)
            ->whereIn('entry_id', $ids)
            ->orderBy('tag_id')
            ->get(['entry_id', 'tag_id']);
        foreach ($rows as $row) {
            $out[(int) $row->entry_id][] = (int) $row->tag_id;
        }

        return $out;
    }

    /**
     * Тег-связи удаляются вместе с тегами филиала (их нужно пересоздать через syncEntryTags).
     *
     * @return array<int, int> oldId => newId
     */
    public function replaceAllForBranch(int $branchId, array $tags): array
    {
        return DB::transaction(function () use ($branchId, $tags): array {
            $existingIds = CMP_BranchTag::query()
                ->where('branch_id', $branchId)
                ->pluck('id')
                ->all();
            if ($existingIds !== []) {
                CMP_BranchEntryTag::query()->whereIn('tag_id', $existingIds)->delete();
                CMP_BranchTag::query()->whereIn('id', $existingIds)->delete();
            }

            $map = [];
            foreach ($tags as $tag) {
                if (! $tag instanceof BranchTag) {
                    continue;
                }

                $row = CMP_BranchTag::query()->create([
                    'branch_id' => $branchId,
                    'code' => $tag->code(),
                    'label' => $tag->label(),
                    'color' => $tag->color(),
                    'is_active' => $tag->isActive(),
                    'sort_order' => $tag->sortOrder(),
                ]);
                $map[$tag->id()] = (int) $row->id;
            }

            return $map;
        });
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map(static fn ($id) => (int) $id, $ids),
            static fn (int $id): bool => $id > 0,
        )));
    }
}
