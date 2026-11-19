<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Tag;
use App\Domain\Supporting\Catalog\Repository\TagRepository;
use App\Infrastructure\Catalog\Mapper\CatalogTagMapper;
use App\Infrastructure\Catalog\Model\CAT_Tag;
use Illuminate\Support\Facades\DB;

final class EloquentTagRepository implements TagRepository
{
    public function __construct(
        private readonly CatalogTagMapper $mapper,
    ) {}

    public function findAllActiveOrdered(): array
    {
        return CAT_Tag::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CAT_Tag $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findAllOrdered(): array
    {
        return CAT_Tag::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CAT_Tag $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findById(int $id): ?Tag
    {
        $row = CAT_Tag::query()->find($id);

        return $row instanceof CAT_Tag ? $this->mapper->toDomain($row) : null;
    }

    public function findByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        return CAT_Tag::query()
            ->whereIn('id', $ids)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CAT_Tag $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findTagIdsByProductId(int $productId): array
    {
        return $this->findTagIdsByCatalogItemId($productId);
    }

    public function findTagIdsBySetId(int $setId): array
    {
        return $this->findTagIdsByCatalogItemId($setId);
    }

    public function save(?int $id, string $code, string $label, string $color, bool $isActive): Tag
    {
        $code = trim($code);
        $exists = CAT_Tag::query()
            ->where('code', $code)
            ->when($id !== null, fn ($query) => $query->where('id', '!=', $id))
            ->exists();
        if ($exists) {
            throw new \InvalidArgumentException('Такой код тега уже есть.');
        }

        $row = $id === null ? new CAT_Tag() : CAT_Tag::query()->find($id);
        if (! $row instanceof CAT_Tag) {
            throw new \InvalidArgumentException('Тег не найден.');
        }

        $row->fill([
            'code' => $code,
            'label' => $label,
            'color' => $color,
            'is_active' => $isActive,
        ]);
        if ($id === null) {
            $row->sort_order = 0;
        }
        $row->save();

        return $this->mapper->toDomain($row);
    }

    /**
     * @return list<int>
     */
    private function findTagIdsByCatalogItemId(int $catalogItemId): array
    {
        return DB::table('CAT_item_tags')
            ->where('item_id', $catalogItemId)
            ->orderBy('tag_id')
            ->pluck('tag_id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }
}
