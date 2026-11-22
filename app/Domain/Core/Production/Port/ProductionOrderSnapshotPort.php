<?php

namespace App\Domain\Core\Production\Port;

/**
 * Снимок заказа для кухни. Production Domain не импортирует Order.
 *
 * @phpstan-type ProductionOrderLine array{
 *     product_id: int,
 *     name: string,
 *     quantity: int,
 *     line_type: string
 * }
 * @phpstan-type ProductionOrderSnapshot array{
 *     id: int,
 *     number: string|null,
 *     branch_id: int|null,
 *     status: string,
 *     comment: string|null,
 *     lines: list<ProductionOrderLine>
 * }
 */
interface ProductionOrderSnapshotPort
{
    /**
     * @return ProductionOrderSnapshot|null
     */
    public function find(int $orderId): ?array;

    /**
     * @param  list<int>  $orderIds
     * @return array<int, ProductionOrderSnapshot> map orderId → snapshot
     */
    public function findMany(array $orderIds): array;
}
