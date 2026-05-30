<?php

namespace App\Application\Operations\Shopping\Contracts;

interface AdminShoppingSessionReadRepository
{
    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function paginateActiveCarts(
        int $page = 1,
        int $perPage = 25,
        ?int $clientId = null,
        ?int $sessionId = null,
        ?string $publicId = null,
        ?string $orderId = null,
    ): array;
}
