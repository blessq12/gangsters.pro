<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Mapper\CompanyMapper;
use App\Infrastructure\Company\Model\CMP_Company;

final class EloquentCompanyRepository implements CompanyRepository
{
    public function __construct(
        private readonly CompanyMapper $mapper,
    ) {}

    public function findPublic(): ?Company
    {
        $row = CMP_Company::query()->find(self::SINGLETON_ID);

        return $row instanceof CMP_Company ? $this->mapper->toDomain($row) : null;
    }
}
