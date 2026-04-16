<?php

namespace App\Domain\Order\Contracts;

interface CatalogItemSnapshotProvider
{
    /**
     * @param  int[]  $productIds
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     sku: string,
     *     price: int,
     *     media: array<int, array<string, mixed>>
     * }>
     */
    public function getActiveSnapshotsByIds(array $productIds): array;
}
