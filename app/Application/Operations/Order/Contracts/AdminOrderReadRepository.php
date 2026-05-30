<?php

namespace App\Application\Operations\Order\Contracts;

use App\Domain\Order\Entities\Order;

interface AdminOrderReadRepository
{
    /**
     * @return array{items: Order[], total: int}
     */
    public function paginate(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $phone = null,
        ?int $clientId = null,
        int $page = 1,
        int $perPage = 25,
    ): array;

    public function findById(string $id): ?Order;
}
