<?php

namespace App\Domain\Core\Order\Port;

use App\Domain\Core\Order\Entity\OrderCommercialRules;

/**
 * Снимок коммерческих правил филиала. SoT хранится в Branch; Order только читает/применяет.
 */
interface OrderBranchCommercialRulesPort
{
    public function findByBranch(int $branchId): ?OrderCommercialRules;
}
