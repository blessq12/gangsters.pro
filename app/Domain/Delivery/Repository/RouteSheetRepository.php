<?php

namespace App\Domain\Delivery\Repository;

use App\Domain\Delivery\Entity\RouteSheet;

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
