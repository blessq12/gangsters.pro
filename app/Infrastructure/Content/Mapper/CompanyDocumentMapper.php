<?php

namespace App\Infrastructure\Content\Mapper;

use App\Domain\Content\Entity\CompanyDocument;
use App\Infrastructure\Content\Model\CMP_CompanyDocument;

final class CompanyDocumentMapper
{
    public function toDomain(CMP_CompanyDocument $row): CompanyDocument
    {
        return new CompanyDocument(
            id: (int) $row->id,
            slug: (string) $row->slug,
            name: (string) $row->name,
            content: $row->content !== null ? (string) $row->content : null,
        );
    }
}
