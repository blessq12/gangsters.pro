<?php

namespace App\Application\Catalog\Contracts;

use App\Domain\Product\Entity\Product;

interface AdminProductReadRepository
{
    /**
     * @return array{items: Product[], total: int}
     */
    public function paginate(
        ?string $search,
        ?string $status,
        int $page,
        int $perPage,
    ): array;
}
