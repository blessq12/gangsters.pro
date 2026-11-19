<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\Branch;
use App\Infrastructure\Company\Model\CMP_Branch;

final class BranchMapper
{
    public function toDomain(CMP_Branch $row): Branch
    {
        return new Branch(
            id: (int) $row->id,
            companyId: (int) $row->company_id,
            name: (string) $row->name,
            isActive: (bool) $row->is_active,
        );
    }
}
