<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\CompanyDocument;

interface CompanyDocumentRepository
{
    /**
     * @return list<CompanyDocument>
     */
    public function findAllOrdered(): array;
}
