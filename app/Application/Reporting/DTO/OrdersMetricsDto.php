<?php

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\ValueObject\MetricsPeriod;

final readonly class OrdersMetricsDto implements MetricsSectionDto
{
    /**
     * @param  array<string, int>  $ordersPipeline
     * @param  array{
     *     registered_orders: int,
     *     guest_orders: int,
     *     yandex_orders: int,
     *     site_orders: int
     * }  $channelStats
     * @param  list<array{
     *     id: string,
     *     created_at: string,
     *     status: string,
     *     total: int,
     *     customer_name: string,
     *     client_id: ?int
     * }>  $recentOrders
     */
    public function __construct(
        public MetricsPeriod $period,
        public array $ordersPipeline,
        public array $channelStats,
        public array $recentOrders,
    ) {}
}
