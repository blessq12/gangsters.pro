<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\CompanyLegalInfo;
use App\Domain\Content\Repository\CompanyLegalRepository;
use App\Domain\Content\Repository\CompanyRepository;
use App\Infrastructure\Content\Mapper\CompanyLegalMapper;
use App\Infrastructure\Content\Model\CMP_CompanyLegal;

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
