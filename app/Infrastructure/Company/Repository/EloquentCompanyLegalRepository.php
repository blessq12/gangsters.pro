<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\CompanyLegalInfo;
use App\Domain\Supporting\Company\Repository\CompanyLegalRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
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

    public function save(CompanyLegalInfo $info): void
    {
        $row = CMP_CompanyLegal::query()->firstOrNew([
            'company_id' => CompanyRepository::SINGLETON_ID,
        ]);
        $row->fill([
            'full_name' => $info->fullName(),
            'inn' => $info->inn(),
            'ogrn' => $info->ogrn(),
        ]);
        $row->save();
    }
}
