<?php

namespace App\Infrastructure\Menu\Mapper;

use App\Domain\Supporting\Menu\Entity\BranchTag;
use App\Infrastructure\Menu\Model\BranchTagModel;

final class BranchTagMapper
{
    public function toDomain(BranchTagModel $row): BranchTag
    {
        return new BranchTag(
            id: (int) $row->id,
            branchId: (int) $row->branch_id,
            code: (string) $row->code,
            label: (string) $row->label,
            color: (string) $row->color,
            isActive: (bool) $row->is_active,
            sortOrder: (int) $row->sort_order,
        );
    }
}
