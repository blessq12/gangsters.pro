<?php

namespace App\Application\Notifications\Contracts;

interface NotificationDeliveryReadRepository
{
    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        ?string $channel = null,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        int $page = 1,
        int $perPage = 25,
    ): array;
}
