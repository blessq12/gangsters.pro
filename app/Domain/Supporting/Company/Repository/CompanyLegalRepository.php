<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\CompanyLegalInfo;

interface CompanyLegalRepository
{
    public function findPublic(): ?CompanyLegalInfo;

    public function save(CompanyLegalInfo $info): void;
}
