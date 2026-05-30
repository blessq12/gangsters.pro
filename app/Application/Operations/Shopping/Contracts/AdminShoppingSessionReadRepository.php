<?php

namespace App\Application\Operations\Shopping\Contracts;

interface AdminShoppingSessionReadRepository
{
    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function paginateActiveCarts(int $page = 1, int $perPage = 25): array;
}
