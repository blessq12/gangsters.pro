<?php

namespace App\Application\Dashboard\Query;

use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;
use DateTimeImmutable;

final class GetDashboardSummaryUseCase
{
    public function __construct(
        private readonly PaymentRepository $payments,
        private readonly OrderRepository $orders,
        private readonly RouteSheetRepository $sheets,
        private readonly KitchenTicketRepository $tickets,
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
        $normalizedBranch = $branchId !== null && $branchId > 0 ? $branchId : null;

        $money = $this->payments->summarizeFiltered(
            $createdFrom,
            $createdTo,
            null,
            null,
            $normalizedBranch,
        );

        $byStatus = $this->orders->countByStatusFiltered(
            $normalizedBranch,
            $createdFrom,
            $createdTo,
        );
        $createdCount = 0;
        foreach ($byStatus as $count) {
            $createdCount += (int) $count;
        }

        $ops = $this->orders->countOpsSnapshot($normalizedBranch);
        $ticketCounts = $this->tickets->countActiveByStatus($normalizedBranch);

        return [
            'period' => [
                'from' => $createdFrom?->format(DATE_ATOM),
                'to' => $createdTo?->format(DATE_ATOM),
            ],
            'branch_id' => $normalizedBranch,
            'money' => $money,
            'orders' => [
                'created_count' => $createdCount,
                'delivered_count' => (int) ($byStatus['delivered'] ?? 0),
                'cancelled_count' => (int) ($byStatus['cancelled'] ?? 0),
                'by_status' => $byStatus,
            ],
            'ops' => [
                'new' => (int) ($ops['new'] ?? 0),
                'preparing' => (int) ($ops['preparing'] ?? 0),
                'ready' => (int) ($ops['ready'] ?? 0),
                'ready_courier' => (int) ($ops['ready_courier'] ?? 0),
                'in_transit' => (int) ($ops['in_transit'] ?? 0),
                'open_route_sheets' => $this->sheets->countOpen($normalizedBranch),
                'queued_tickets' => (int) ($ticketCounts['queued'] ?? 0),
                'started_tickets' => (int) ($ticketCounts['started'] ?? 0),
            ],
        ];
    }
}
