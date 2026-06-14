<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Company\Repository\CompanyDocumentRepository;
use App\Domain\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Mapper\CompanyDocumentMapper;
use App\Infrastructure\Company\Model\CMP_CompanyDocument;

final class EloquentCompanyDocumentRepository implements CompanyDocumentRepository
{
    public function __construct(
        private readonly CompanyDocumentMapper $mapper,
    ) {}

    public function findAllOrdered(): array
    {
        return CMP_CompanyDocument::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->orderBy('id')
            ->get()
            ->map(fn (CMP_CompanyDocument $row) => $this->mapper->toDomain($row))
            ->all();
    }
}
