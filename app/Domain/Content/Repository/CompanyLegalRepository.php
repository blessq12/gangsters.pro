<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\CompanyLegalInfo;

interface CompanyLegalRepository
{
    public function findPublic(): ?CompanyLegalInfo;

    public function save(CompanyLegalInfo $info): void;
}
