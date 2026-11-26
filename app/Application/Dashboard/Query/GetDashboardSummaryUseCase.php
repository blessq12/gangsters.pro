<?php

namespace App\Application\Dashboard\Query;

use App\Application\Dashboard\Port\DashboardSummaryPort;
use DateTimeImmutable;

final class GetDashboardSummaryUseCase
{
    public function __construct(
        private readonly DashboardSummaryPort $dashboard,
    ) {}

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
    public function execute(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?int $branchId = null,
    ): array {
        return $this->dashboard->summarize($createdFrom, $createdTo, $branchId);
    }
}
