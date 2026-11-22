<?php

namespace Tests\Application\Core\Support;

use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Repository\BranchRepository;

final class InMemoryBranchRepository implements BranchRepository
{
    /** @var array<int, Branch> */
    private array $items = [];

    private int $nextId = 1;

    public function findById(int $id): ?Branch
    {
        return $this->items[$id] ?? null;
    }

    public function listByCompany(int $companyId): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (Branch $branch): bool => $branch->companyId() === $companyId,
        ));
    }

    public function listPageByCompany(int $companyId, int $offset, int $limit): array
    {
        return array_slice($this->listByCompany($companyId), $offset, $limit);
    }

    public function countByCompany(int $companyId): int
    {
        return count($this->listByCompany($companyId));
    }

    public function save(Branch $branch): Branch
    {
        $id = $branch->id();
        if ($id < 1) {
            $id = $this->nextId++;
            $branch = new Branch(
                id: $id,
                companyId: $branch->companyId(),
                name: $branch->name(),
                isActive: $branch->isActive(),
                schedule: $branch->schedule(),
                city: $branch->city(),
                street: $branch->street(),
                house: $branch->house(),
                comment: $branch->comment(),
                latitude: $branch->latitude(),
                longitude: $branch->longitude(),
                phone: $branch->phone(),
            );
        } else {
            $this->nextId = max($this->nextId, $id + 1);
        }

        $this->items[$id] = $branch;

        return $branch;
    }
}
