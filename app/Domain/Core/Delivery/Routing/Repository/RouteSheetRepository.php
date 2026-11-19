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

    public function save(RouteSheet $sheet): void;
}
