<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\CompanyDocument;
use App\Infrastructure\Company\Model\CompanyDocumentModel;

final class CompanyDocumentMapper
{
    public function toDomain(CompanyDocumentModel $row): CompanyDocument
    {
        return new CompanyDocument(
            id: (int) $row->id,
            slug: (string) $row->slug,
            name: (string) $row->name,
            content: $row->content !== null ? (string) $row->content : null,
        );
    }
}
