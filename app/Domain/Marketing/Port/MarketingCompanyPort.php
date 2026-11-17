<?php

namespace App\Domain\Marketing\Port;

/**
 * Чтение данных компании для витрины: только массивы, без сущностей Company.
 *
 * Снимок компании: {id, name, phone, email, socials, schedule}
 */
interface MarketingCompanyPort
{
    /**
     * @return array<string, mixed>|null
     */
    public function findSnapshotById(int $companyId): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(): array;
}
