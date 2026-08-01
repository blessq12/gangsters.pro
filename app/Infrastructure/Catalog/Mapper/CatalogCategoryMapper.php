<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Catalog\Entity\Category;
use App\Infrastructure\Catalog\Model\PRD_Category;

final class CatalogCategoryMapper
{
    public function toDomain(PRD_Category $row): Category
    {
        return new Category(
            id: (int) $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            sortOrder: (int) $row->sort_order,
            isActive: (bool) $row->is_active,
            isAccompanying: (bool) $row->is_accompanying,
        );
    }
}
