<?php

namespace App\Domain\Supporting\Company\Port;

/**
 * Клонирование политики акций между филиалами. Company не владеет PromotionPolicy.
 *
 * @phpstan-type PolicyFlatFields array<string, mixed>
 */
interface CompanyPromotionPolicyClonePort
{
    /**
     * @return PolicyFlatFields|null
     */
    public function exportFlatFields(int $branchId): ?array;

    /**
     * @param  PolicyFlatFields  $fields
     */
    public function importFlatFields(int $branchId, array $fields): void;

    public function existsForBranch(int $branchId): bool;
}
