<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Infrastructure\Company\Mapper\BranchMapper;
use App\Infrastructure\Company\Model\CMP_Branch;

final class EloquentBranchRepository implements BranchRepository
{
    public function __construct(
        private readonly BranchMapper $mapper,
    ) {}

    public function findById(int $id): ?Branch
    {
        $row = CMP_Branch::query()->find($id);

        return $row instanceof CMP_Branch ? $this->mapper->toDomain($row) : null;
    }

    public function listByCompany(int $companyId): array
    {
        return CMP_Branch::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get()
            ->map(fn (CMP_Branch $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(Branch $branch): Branch
    {
        if ($branch->id() < 1) {
            $row = new CMP_Branch();
        } else {
            $row = CMP_Branch::query()->find($branch->id());
            if (! $row instanceof CMP_Branch) {
                throw new \InvalidArgumentException('Филиал не найден.');
            }
        }

        $row->fill([
            'company_id' => $branch->companyId(),
            'name' => $branch->name(),
            'is_active' => $branch->isActive(),
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
