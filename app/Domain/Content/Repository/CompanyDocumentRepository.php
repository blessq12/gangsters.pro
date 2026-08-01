<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\CompanyDocument;

interface CompanyDocumentRepository
{
    /**
     * @return list<CompanyDocument>
     */
    public function findAllOrdered(): array;
}
