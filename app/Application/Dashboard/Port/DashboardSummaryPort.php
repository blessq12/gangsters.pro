<?php

namespace App\Application\Dashboard\Port;

use DateTimeImmutable;

/**
 * Read-модель дашборда: composition чужих BC живёт в Infrastructure-адаптере.
 */
interface DashboardSummaryPort
{
    /**
     * @return array{
     *     period: array{from: string|null, to: string|null},
     *     branch_id: int|null,
     *     money: array<string, mixed>,
     *     orders: array{
     *         created_count: int,
     *         delivered_count: int,
     *         cancelled_count: int,
     *         by_status: array<string, int>
     *     },
     *     ops: array{
     *         new: int,
     *         preparing: int,
     *         ready: int,
     *         ready_courier: int,
     *         in_transit: int,
     *         open_route_sheets: int,
     *         queued_tickets: int,
     *         started_tickets: int
     *     }
     * }
     */
    public function summarize(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?int $branchId = null,
    ): array;
}
