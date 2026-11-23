<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\CompanyDocument;

interface CompanyDocumentRepository
{
    public function findById(int $id): ?CompanyDocument;

    /**
     * @return list<CompanyDocument>
     */
    public function findAllOrdered(): array;

    /**
     * @return list<CompanyDocument>
     */
    public function listPage(int $offset, int $limit): array;

    public function countAll(): int;

    public function save(?int $id, string $name, ?string $content): CompanyDocument;
}
