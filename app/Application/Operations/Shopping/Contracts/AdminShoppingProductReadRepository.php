<?php

namespace App\Application\Operations\Shopping\Contracts;

interface AdminShoppingProductReadRepository
{
    /**
     * @param  int[]  $ids
     * @return array<int, array{id: int, name: string, price_kopecks: int|null, status: string|null}>
     */
    public function findSummariesByIds(array $ids): array;
}
