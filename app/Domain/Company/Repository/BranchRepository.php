<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\Branch;

interface BranchRepository
{
    public function findById(int $id): ?Branch;

    /**
     * @return list<Branch>
     */
    public function listByCompany(int $companyId): array;

    /**
     * Создать (id &lt; 1) или обновить филиал. Возвращает сохранённую сущность с id.
     */
    public function save(Branch $branch): Branch;
}
