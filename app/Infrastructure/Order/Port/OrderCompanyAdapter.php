<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Order\Port\OrderCompanyPort;

/**
 * Order → Company: снимки филиала и кухни.
 * Таблиц филиалов и кухонь пока нет (отдельный план миграций), поэтому снимков нет — всегда null.
 */
final class OrderCompanyAdapter implements OrderCompanyPort
{
    public function findBranchSnapshotById(int $branchId): ?array
    {
        return null;
    }

    public function findKitchenSnapshotByBranchId(int $branchId): ?array
    {
        return null;
    }
}
