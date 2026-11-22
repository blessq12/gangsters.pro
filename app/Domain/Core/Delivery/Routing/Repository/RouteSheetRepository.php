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

    /**
     * Листы, видимые курьеру: свои активные + свободные released без курьера.
     *
     * @return list<RouteSheet>
     */
    public function listVisibleToCourierPage(int $courierId, int $offset, int $limit): array;

    public function countVisibleToCourier(int $courierId): int;

    public function save(RouteSheet $sheet): void;
}
