<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Infrastructure\Company\Model\CMP_BranchMenuItem;

final class BranchMenuMapper
{
    public function toDomain(CMP_BranchMenuItem $row): BranchMenuItem
    {
        return new BranchMenuItem(
            branchId: (int) $row->branch_id,
            catalogItemId: (int) $row->catalog_item_id,
            isAvailable: (bool) $row->is_available,
            priceKopecks: (int) $row->price_kopecks,
            isGiftCandidate: (bool) ($row->is_gift_candidate ?? false),
            id: (int) $row->id,
        );
    }
}
