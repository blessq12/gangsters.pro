<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\CompanyDocument;

interface CompanyDocumentRepository
{
    /**
     * @return list<CompanyDocument>
     */
    public function findAllOrdered(): array;

    public function save(?int $id, string $name, ?string $content): CompanyDocument;
}
