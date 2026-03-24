<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Company;

interface CompanyRepository
{
    public function first(): ?Company;
}

