<?php

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\ValueObject\MetricsPeriod;

final readonly class FinanceMetricsDto implements MetricsSectionDto
{
    /**
     * @param  array{
     *     paid_revenue: int,
     *     gmv: int,
     *     orders_count: int,
     *     paid_orders_count: int,
     *     average_check: int,
     *     previous_paid_revenue: int,
     *     previous_gmv: int,
     *     previous_orders_count: int,
     *     previous_paid_orders_count: int,
     *     previous_average_check: int
     * }  $revenueKpi
     * @param  array{labels: list<string>, values: list<int>}  $revenueTrend
     * @param  array{labels: list<string>, values: list<int>}  $ordersCountTrend
     * @param  array{labels: list<string>, values: list<int>}  $deliveryMix
     * @param  array{labels: list<string>, values: list<int>}  $paymentMix
     */
    public function __construct(
        public MetricsPeriod $period,
        public array $revenueKpi,
        public array $revenueTrend,
        public array $ordersCountTrend,
        public array $deliveryMix,
        public array $paymentMix,
    ) {}
}
