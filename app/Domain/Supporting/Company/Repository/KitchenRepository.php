<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\Kitchen;

interface KitchenRepository
{
    public function findById(int $id): ?Kitchen;

    /**
     * Кухни всех филиалов компании.
     *
     * @return list<Kitchen>
     */
    public function listByCompany(int $companyId): array;

    /**
     * Создать (id &lt; 1) или обновить кухню. Возвращает сохранённую сущность с id.
     */
    public function save(Kitchen $kitchen): Kitchen;
}
