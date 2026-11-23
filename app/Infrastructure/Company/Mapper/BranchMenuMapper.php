<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Infrastructure\Company\Model\BranchMenuItemModel;

final class BranchMenuMapper
{
    public function toDomain(BranchMenuItemModel $row): BranchMenuItem
    {
        return new BranchMenuItem(
            branchId: (int) $row->branch_id,
            catalogItemId: (int) $row->catalog_item_id,
            isAvailable: (bool) $row->is_available,
            priceKopecks: (int) $row->price_kopecks,
            id: (int) $row->id,
        );
    }
}
