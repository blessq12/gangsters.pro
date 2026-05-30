<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Application\Catalog\Contracts\AdminProductReadRepository as AdminProductReadRepositoryContract;
use App\Domain\Product\Entity\Product as ProductEntity;
use App\Domain\Product\Repository\ProductRepository;
use App\Infrastructure\Product\Model\PRD_Product;

final class AdminProductReadRepository implements AdminProductReadRepositoryContract
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    public function paginate(
        ?string $search,
        ?string $status,
        int $page,
        int $perPage,
    ): array {
        $query = PRD_Product::query()->orderByDesc('updated_at');

        if ($status === ProductEntity::STATUS_ACTIVE) {
            $query->where('status', ProductEntity::STATUS_ACTIVE);
        } elseif ($status === ProductEntity::STATUS_ARCHIVED) {
            $query->where('status', ProductEntity::STATUS_ARCHIVED);
        }

        if ($search !== null && $search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->where('name', 'like', $term)
                    ->orWhere('articul', 'like', $term);
            });
        }

        $total = (int) $query->count();
        $ids = $query
            ->forPage(max(1, $page), max(1, $perPage))
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $items = $ids === [] ? [] : $this->products->findByIds($ids);
        $byId = [];
        foreach ($items as $product) {
            $byId[$product->id()] = $product;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return [
            'items' => $ordered,
            'total' => $total,
        ];
    }
}
