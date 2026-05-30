<?php

namespace App\Infrastructure\Operations\Repository;

use App\Application\Operations\Shopping\Contracts\AdminShoppingProductReadRepository;
use App\Infrastructure\Product\Model\PRD_Product;

final class EloquentAdminShoppingProductReadRepository implements AdminShoppingProductReadRepository
{
    public function findSummariesByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($id): int => (int) $id, $ids),
            static fn (int $id): bool => $id > 0,
        )));

        if ($ids === []) {
            return [];
        }

        $summaries = [];
        foreach (PRD_Product::query()->whereIn('id', $ids)->get(['id', 'name', 'price', 'status']) as $model) {
            /** @var PRD_Product $model */
            $summaries[(int) $model->id] = [
                'id' => (int) $model->id,
                'name' => trim((string) ($model->name ?? '')),
                'price_kopecks' => $model->price !== null ? (int) $model->price : null,
                'status' => $model->status !== null ? (string) $model->status : null,
            ];
        }

        return $summaries;
    }
}
