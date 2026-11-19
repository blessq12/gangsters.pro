<?php

namespace Tests\Application\Core\Support;

use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Domain\Supporting\Company\Repository\KitchenRepository;

final class InMemoryKitchenRepository implements KitchenRepository
{
    /** @var array<int, Kitchen> */
    private array $kitchens = [];

    public function findById(int $id): ?Kitchen
    {
        return $this->kitchens[$id] ?? null;
    }

    public function listByCompany(int $companyId): array
    {
        return array_values($this->kitchens);
    }

    public function listPageByCompany(int $companyId, int $offset, int $limit): array
    {
        return array_slice(array_values($this->kitchens), $offset, $limit);
    }

    public function countByCompany(int $companyId): int
    {
        return count($this->kitchens);
    }

    public function save(Kitchen $kitchen): Kitchen
    {
        $this->kitchens[$kitchen->id()] = $kitchen;

        return $kitchen;
    }
}
