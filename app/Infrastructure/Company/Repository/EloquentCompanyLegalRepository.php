<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Company\Entity\CompanyLegalInfo;
use App\Domain\Company\Repository\CompanyLegalRepository;
use App\Domain\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Mapper\CompanyLegalMapper;
use App\Infrastructure\Company\Model\CMP_CompanyLegal;

final class EloquentCompanyLegalRepository implements CompanyLegalRepository
{
    public function __construct(
        private readonly CompanyLegalMapper $mapper,
    ) {}

    public function findPublic(): ?CompanyLegalInfo
    {
        $row = CMP_CompanyLegal::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->first();

        return $row instanceof CMP_CompanyLegal ? $this->mapper->toDomain($row) : null;
    }
}
