<?php

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\ValueObject\MetricsPeriod;
use Carbon\CarbonImmutable;

final readonly class BusinessMetricsSnapshotDto
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
     * @param  array{
     *     new_clients: int,
     *     total_clients: int,
     *     marketing_consent: int,
     *     repeat_clients: int,
     *     previous_new_clients: int
     * }  $clientsKpi
     * @param  array<string, int>  $ordersPipeline
     * @param  array{labels: list<string>, values: list<int>}  $revenueTrend
     * @param  array{labels: list<string>, values: list<int>}  $ordersCountTrend
     * @param  array{labels: list<string>, values: list<int>}  $deliveryMix
     * @param  array{labels: list<string>, values: list<int>}  $paymentMix
     * @param  list<array{product_original_id: ?int, product_name: string, quantity: int, revenue: int}>  $topProducts
     * @param  list<array{client_id: int, client_name: string, orders_count: int, revenue: int}>  $topClients
     * @param  list<array{
     *     id: string,
     *     created_at: string,
     *     status: string,
     *     total: int,
     *     customer_name: string,
     *     client_id: ?int
     * }>  $recentOrders
     * @param  array{
     *     active_sessions: int,
     *     cart_lines: int,
     *     checkout_drafts: int
     * }  $shoppingFunnel
     * @param  array{
     *     registered_orders: int,
     *     guest_orders: int,
     *     yandex_orders: int,
     *     site_orders: int
     * }  $channelStats
     */
    public function __construct(
        public MetricsPeriod $period,
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public array $revenueKpi,
        public array $clientsKpi,
        public array $ordersPipeline,
        public array $revenueTrend,
        public array $ordersCountTrend,
        public array $deliveryMix,
        public array $paymentMix,
        public array $topProducts,
        public array $topClients,
        public array $recentOrders,
        public array $shoppingFunnel,
        public array $channelStats,
    ) {}
}
