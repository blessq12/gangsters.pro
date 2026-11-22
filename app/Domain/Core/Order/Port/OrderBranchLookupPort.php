<?php

namespace App\Domain\Core\Order\Port;

/**
 * Активный филиал для оформления заказа. Order Domain не импортирует Company.
 *
 * @phpstan-type OrderBranchSnapshot array{
 *     id: int,
 *     city: string|null
 * }
 */
interface OrderBranchLookupPort
{
    /**
     * Активный филиал или исключение, если недоступен.
     *
     * @return OrderBranchSnapshot
     */
    public function requireActiveBranch(int $branchId): array;
}
