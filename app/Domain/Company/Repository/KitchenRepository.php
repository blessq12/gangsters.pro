<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\Kitchen;

interface KitchenRepository
{
    public function findById(int $id): ?Kitchen;

    /**
     * Кухни всех филиалов компании.
     *
     * @return list<Kitchen>
     */
    public function listByCompany(int $companyId): array;
}
