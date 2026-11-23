<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\CompanyLegalInfo;
use App\Domain\Supporting\Company\Repository\CompanyLegalRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Mapper\CompanyLegalMapper;
use App\Infrastructure\Company\Model\CompanyLegalModel;

final class EloquentCompanyLegalRepository implements CompanyLegalRepository
{
    public function __construct(
        private readonly CompanyLegalMapper $mapper,
    ) {}

    public function findPublic(): ?CompanyLegalInfo
    {
        $row = CompanyLegalModel::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->first();

        return $row instanceof CompanyLegalModel ? $this->mapper->toDomain($row) : null;
    }

    public function save(CompanyLegalInfo $info): void
    {
        $row = CompanyLegalModel::query()->firstOrNew([
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
