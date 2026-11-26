<?php

namespace App\Domain\Supporting\Company\Port;

/**
 * Read ACL: Company читает снимок филиалов, не BranchRepository.
 */
interface CompanyBranchLookupPort
{
    /**
     * Полные снимки филиалов компании (админка / витрина).
     *
     * @return list<array<string, mixed>>
     */
    public function listSnapshotsByCompany(int $companyId): array;

    /**
     * Краткие снимки для выбора филиала на витрине.
     *
     * @return list<array{id: int, name: string, is_active: bool, phone: string|null}>
     */
    public function listSummariesByCompany(int $companyId): array;
}
