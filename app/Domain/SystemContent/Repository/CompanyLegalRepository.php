<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\CompanyLegal;

interface CompanyLegalRepository
{
    public function first(): ?CompanyLegal;

    public function save(CompanyLegal $legal): CompanyLegal;
}

