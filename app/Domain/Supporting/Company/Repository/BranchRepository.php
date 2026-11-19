<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\Branch;

interface BranchRepository
{
    public function findById(int $id): ?Branch;

    /**
     * @return list<Branch>
     */
    public function listByCompany(int $companyId): array;

    /**
     * @return list<Branch>
     */
    public function listPageByCompany(int $companyId, int $offset, int $limit): array;

    public function countByCompany(int $companyId): int;

    /**
     * Создать (id &lt; 1) или обновить филиал. Возвращает сохранённую сущность с id.
     */
    public function save(Branch $branch): Branch;
}
