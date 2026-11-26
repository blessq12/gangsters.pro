<?php

namespace App\Domain\Supporting\Branch\Port;

/**
 * Клонирование коммерческих правил между филиалами. SoT — Branch.
 *
 * @phpstan-type RulesFlatFields array<string, mixed>
 */
interface BranchCommercialRulesClonePort
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
