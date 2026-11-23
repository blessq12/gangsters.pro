<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\BranchCategory;
use App\Domain\Supporting\Company\Entity\BranchCategoryItem;
use App\Infrastructure\Company\Model\BranchCategoryModel;
use App\Infrastructure\Company\Model\BranchCategoryItemModel;

final class BranchCategoryMapper
{
    public function toDomain(BranchCategoryModel $row): BranchCategory
    {
        return new BranchCategory(
            id: (int) $row->id,
            branchId: (int) $row->branch_id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            sortOrder: (int) $row->sort_order,
            isActive: (bool) $row->is_active,
            isAccompanying: (bool) ($row->is_accompanying ?? false),
            isComplement: (bool) ($row->is_complement ?? false),
            isGift: (bool) ($row->is_gift ?? false),
        );
    }

    public function itemToDomain(BranchCategoryItemModel $row): BranchCategoryItem
    {
        return new BranchCategoryItem(
            categoryId: (int) $row->category_id,
            entryKind: $row->entry_kind,
            entryId: (int) $row->entry_id,
            sortOrder: (int) $row->sort_order,
            id: (int) $row->id,
        );
    }
}
