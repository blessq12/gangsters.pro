<?php

namespace App\Domain\Core\Delivery\Routing\Repository;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;

interface RouteSheetRepository
{
    public function findById(int $id): ?RouteSheet;

    public function findOpenByOrderId(int $orderId): ?RouteSheet;

    /**
     * @return list<RouteSheet>
     */
    public function listActive(): array;

    /**
     * @return list<RouteSheet>
     */
    public function listActivePage(int $offset, int $limit): array;

    public function countActive(): int;

    /**
     * @return list<RouteSheet>
     */
    public function listActivePageByBranch(int $branchId, int $offset, int $limit): array;

    public function countActiveByBranch(int $branchId): int;

    /** Открытые листы (released + in_delivery), опционально по филиалу. */
    public function countOpen(?int $branchId = null): int;

    /**
     * Активные листы, назначенные на курьера.
     *
     * @return list<RouteSheet>
     */
    public function listVisibleToCourierPage(int $courierId, int $offset, int $limit): array;

    public function countVisibleToCourier(int $courierId): int;

    public function save(RouteSheet $sheet): void;
}
