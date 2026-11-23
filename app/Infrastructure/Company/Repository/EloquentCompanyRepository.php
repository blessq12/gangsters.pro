<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\Company;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Mapper\CompanyMapper;
use App\Infrastructure\Company\Model\CompanyModel;

final class EloquentCompanyRepository implements CompanyRepository
{
    public function __construct(
        private readonly CompanyMapper $mapper,
    ) {}

    public function findPublic(): ?Company
    {
        $row = CompanyModel::query()->find(self::SINGLETON_ID);

        return $row instanceof CompanyModel ? $this->mapper->toDomain($row) : null;
    }

    public function save(Company $company): void
    {
        $row = CompanyModel::query()->firstOrCreate(
            ['id' => self::SINGLETON_ID],
            [
                'name' => $company->name(),
                'socials' => [],
            ],
        );
        $row->fill([
            'name' => $company->name(),
            'description' => $company->description(),
            'phone' => $company->phone(),
            'email' => $company->email(),
            'socials' => $company->socials()->toArray(),
        ]);
        $row->save();
    }
}
