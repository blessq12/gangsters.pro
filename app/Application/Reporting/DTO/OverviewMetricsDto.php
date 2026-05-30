<?php

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\ValueObject\MetricsPeriod;

final readonly class OverviewMetricsDto implements MetricsSectionDto
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
     * @param  array<string, int>  $ordersPipeline
     */
    public function __construct(
        public MetricsPeriod $period,
        public array $revenueKpi,
        public array $ordersPipeline,
    ) {}
}
