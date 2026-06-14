<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Catalog\Entity\Tag;
use App\Infrastructure\Catalog\Model\PRD_Tag;

final class CatalogTagMapper
{
    public function toDomain(PRD_Tag $row): Tag
    {
        return new Tag(
            id: (int) $row->id,
            code: (string) $row->code,
            label: (string) $row->label,
            color: (string) $row->color,
            isActive: (bool) $row->is_active,
            sortOrder: (int) $row->sort_order,
        );
    }
}
