<?php

namespace App\Domain\Core\Delivery\Routing\Port;

/**
 * Снимок филиала для тарифов/витрины Delivery. Delivery Domain не импортирует Company.
 *
 * @phpstan-type DeliveryBranchAddressSnapshot array{
 *     city: ?string,
 *     street: ?string,
 *     house: ?string,
 *     comment: ?string,
 *     search_line: ?string,
 *     latitude: ?float,
 *     longitude: ?float
 * }
 */
interface DeliveryBranchLookupPort
{
    public function assertBranchExists(int $branchId): void;

    /**
     * @return DeliveryBranchAddressSnapshot|null
     */
    public function findAddressByBranchId(int $branchId): ?array;
}
