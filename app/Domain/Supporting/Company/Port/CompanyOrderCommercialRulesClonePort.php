<?php

namespace App\Domain\Supporting\Company\Port;

/**
 * Клонирование коммерческих правил заказа между филиалами.
 *
 * @phpstan-type RulesFlatFields array<string, mixed>
 */
interface CompanyOrderCommercialRulesClonePort
{
    /**
     * @return RulesFlatFields|null
     */
    public function exportFlatFields(int $branchId): ?array;

    /**
     * @param  RulesFlatFields  $fields
     */
    public function importFlatFields(int $branchId, array $fields): void;

    public function existsForBranch(int $branchId): bool;
}
