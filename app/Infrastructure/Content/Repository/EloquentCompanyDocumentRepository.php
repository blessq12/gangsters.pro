<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Repository\CompanyDocumentRepository;
use App\Domain\Content\Repository\CompanyRepository;
use App\Infrastructure\Content\Mapper\CompanyDocumentMapper;
use App\Infrastructure\Content\Model\CMP_CompanyDocument;

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
