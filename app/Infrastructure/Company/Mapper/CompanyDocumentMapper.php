<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Company\Entity\CompanyDocument;
use App\Infrastructure\Company\Model\CMP_CompanyDocument;

final class CompanyDocumentMapper
{
    public function toDomain(CMP_CompanyDocument $row): CompanyDocument
    {
        return new CompanyDocument(
            id: (int) $row->id,
            key: (string) $row->key,
            title: (string) $row->title,
            content: $row->content !== null ? (string) $row->content : null,
        );
    }
}
