<?php

namespace App\Application\Operations\Client\Contracts;

use App\Domain\Client\Entity\Client;

interface AdminClientReadRepository
{
    /**
     * @return array{items: Client[], total: int}
     */
    public function paginate(
        ?string $search = null,
        int $page = 1,
        int $perPage = 25,
    ): array;

    public function findById(int $id): ?Client;

    /**
     * @return array{id: int, name: string|null, phone: string|null, email: string|null}|null
     */
    public function findProfileSummaryById(int $id): ?array;
}
