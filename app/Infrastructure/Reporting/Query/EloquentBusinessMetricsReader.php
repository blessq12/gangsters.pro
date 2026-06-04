<?php

namespace App\Infrastructure\Reporting\Query;

use App\Application\Reporting\DTO\BusinessMetricsSnapshotDto;
use App\Application\Reporting\DTO\ClientsMetricsDto;
use App\Application\Reporting\DTO\FinanceMetricsDto;
use App\Application\Reporting\DTO\MetricsSectionDto;
use App\Application\Reporting\DTO\OrdersMetricsDto;
use App\Application\Reporting\DTO\OverviewMetricsDto;
use App\Application\Reporting\DTO\StorefrontMetricsDto;
use App\Application\Reporting\Query\BusinessMetricsReader;
use App\Application\Reporting\ValueObject\MetricsPeriod;
use App\Application\Reporting\ValueObject\MetricsSection;
use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\OrderSource;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Reporting\Model\ReportingClientOrderFact;
use App\Support\Order\OrderStatusLabels;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class EloquentBusinessMetricsReader implements BusinessMetricsReader
{
    public function forPeriod(MetricsPeriod $period): BusinessMetricsSnapshotDto
    {
        return Cache::remember(
            $this->cacheKey('full', $period),
            120,
            fn (): BusinessMetricsSnapshotDto => $this->build($period),
        );
    }

    public function forSection(MetricsPeriod $period, MetricsSection $section): MetricsSectionDto
    {
        return match ($section) {
            MetricsSection::Overview => $this->overview($period),
            MetricsSection::Finance => $this->finance($period),
            MetricsSection::Clients => $this->clients($period),
            MetricsSection::Orders => $this->orders($period),
            MetricsSection::Storefront => $this->storefront($period),
        };
    }

    public function overview(MetricsPeriod $period): OverviewMetricsDto
    {
        return Cache::remember(
            $this->cacheKey(MetricsSection::Overview->value, $period),
            120,
            function () use ($period): OverviewMetricsDto {
                $range = $period->currentRange();
                $previousRange = $period->previousRange();

                return new OverviewMetricsDto(
                    period: $period,
                    revenueKpi: $this->revenueKpi($range, $previousRange),
                    ordersPipeline: $this->ordersPipeline(),
                );
            },
        );
    }

    public function finance(MetricsPeriod $period): FinanceMetricsDto
    {
        return Cache::remember(
            $this->cacheKey(MetricsSection::Finance->value, $period),
            120,
            function () use ($period): FinanceMetricsDto {
                $range = $period->currentRange();
                $previousRange = $period->previousRange();

                return new FinanceMetricsDto(
                    period: $period,
                    revenueKpi: $this->revenueKpi($range, $previousRange),
                    revenueTrend: $this->revenueTrend($period, $range),
                    ordersCountTrend: $this->ordersCountTrend($period, $range),
                    deliveryMix: $this->deliveryMix($range),
                    paymentMix: $this->paymentMix($range),
                );
            },
        );
    }

    public function clients(MetricsPeriod $period): ClientsMetricsDto
    {
        return Cache::remember(
            $this->cacheKey(MetricsSection::Clients->value, $period),
            120,
            function () use ($period): ClientsMetricsDto {
                $range = $period->currentRange();
                $previousRange = $period->previousRange();

                return new ClientsMetricsDto(
                    period: $period,
                    clientsKpi: $this->clientsKpi($range, $previousRange),
                    topClients: $this->topClients($range),
                );
            },
        );
    }

    public function orders(MetricsPeriod $period): OrdersMetricsDto
    {
        return Cache::remember(
            $this->cacheKey(MetricsSection::Orders->value, $period),
            120,
            function () use ($period): OrdersMetricsDto {
                $range = $period->currentRange();

                return new OrdersMetricsDto(
                    period: $period,
                    ordersPipeline: $this->ordersPipeline(),
                    channelStats: $this->channelStats($range),
                    recentOrders: $this->recentOrders(),
                );
            },
        );
    }

    public function storefront(MetricsPeriod $period): StorefrontMetricsDto
    {
        return Cache::remember(
            $this->cacheKey(MetricsSection::Storefront->value, $period),
            120,
            function () use ($period): StorefrontMetricsDto {
                $range = $period->currentRange();

                return new StorefrontMetricsDto(
                    period: $period,
                    shoppingFunnel: $this->shoppingFunnel(),
                    topProducts: $this->topProducts($range),
                );
            },
        );
    }

    public function build(MetricsPeriod $period): BusinessMetricsSnapshotDto
    {
        $range = $period->currentRange();
        $previousRange = $period->previousRange();

        return new BusinessMetricsSnapshotDto(
            period: $period,
            from: $range['from'],
            to: $range['to'],
            revenueKpi: $this->revenueKpi($range, $previousRange),
            clientsKpi: $this->clientsKpi($range, $previousRange),
            ordersPipeline: $this->ordersPipeline(),
            revenueTrend: $this->revenueTrend($period, $range),
            ordersCountTrend: $this->ordersCountTrend($period, $range),
            deliveryMix: $this->deliveryMix($range),
            paymentMix: $this->paymentMix($range),
            topProducts: $this->topProducts($range),
            topClients: $this->topClients($range),
            recentOrders: $this->recentOrders(),
            shoppingFunnel: $this->shoppingFunnel(),
            channelStats: $this->channelStats($range),
        );
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $previousRange
     * @return array<string, int>
     */
    private function revenueKpi(array $range, array $previousRange): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return $this->emptyRevenueKpi();
        }

        $current = $this->orderAggregates($range);
        $previous = $this->orderAggregates($previousRange);

        return [
            'paid_revenue' => $current['paid_revenue'],
            'gmv' => $current['gmv'],
            'orders_count' => $current['orders_count'],
            'paid_orders_count' => $current['paid_orders_count'],
            'average_check' => $current['paid_orders_count'] > 0
                ? (int) floor($current['paid_revenue'] / $current['paid_orders_count'])
                : 0,
            'previous_paid_revenue' => $previous['paid_revenue'],
            'previous_gmv' => $previous['gmv'],
            'previous_orders_count' => $previous['orders_count'],
            'previous_paid_orders_count' => $previous['paid_orders_count'],
            'previous_average_check' => $previous['paid_orders_count'] > 0
                ? (int) floor($previous['paid_revenue'] / $previous['paid_orders_count'])
                : 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyRevenueKpi(): array
    {
        return [
            'paid_revenue' => 0,
            'gmv' => 0,
            'orders_count' => 0,
            'paid_orders_count' => 0,
            'average_check' => 0,
            'previous_paid_revenue' => 0,
            'previous_gmv' => 0,
            'previous_orders_count' => 0,
            'previous_paid_orders_count' => 0,
            'previous_average_check' => 0,
        ];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return array{paid_revenue: int, gmv: int, orders_count: int, paid_orders_count: int}
     */
    private function orderAggregates(array $range): array
    {
        $row = $this->ordersInRange($range)
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total), 0) as gmv')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payment_status = ? THEN total ELSE 0 END), 0) as paid_revenue',
                [PaymentStatus::Paid->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END), 0) as paid_orders_count',
                [PaymentStatus::Paid->value],
            )
            ->first();

        return [
            'orders_count' => (int) ($row->orders_count ?? 0),
            'gmv' => (int) ($row->gmv ?? 0),
            'paid_revenue' => (int) ($row->paid_revenue ?? 0),
            'paid_orders_count' => (int) ($row->paid_orders_count ?? 0),
        ];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $previousRange
     * @return array<string, int>
     */
    private function clientsKpi(array $range, array $previousRange): array
    {
        if (! Schema::hasTable('UR_clients')) {
            return [
                'new_clients' => 0,
                'total_clients' => 0,
                'marketing_consent' => 0,
                'repeat_clients' => 0,
                'previous_new_clients' => 0,
            ];
        }

        $newClients = (int) UR_Client::query()
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->count();

        $previousNewClients = (int) UR_Client::query()
            ->whereBetween('created_at', [$previousRange['from'], $previousRange['to']])
            ->count();

        $repeatClients = 0;
        if (Schema::hasTable('reporting_client_order_facts')) {
            $repeatClients = (int) (DB::selectOne(
                'SELECT COUNT(*) as aggregate FROM (
                    SELECT client_id FROM reporting_client_order_facts GROUP BY client_id HAVING COUNT(*) > 1
                ) as repeat_clients',
            )->aggregate ?? 0);
        }

        return [
            'new_clients' => $newClients,
            'total_clients' => (int) UR_Client::query()->count(),
            'marketing_consent' => (int) UR_Client::query()->where('consent_marketing', true)->count(),
            'repeat_clients' => $repeatClients,
            'previous_new_clients' => $previousNewClients,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function ordersPipeline(): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return [];
        }

        $byStatus = ORD_Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count): int => (int) $count)
            ->all();

        $counts = [];
        foreach (OrderStatusLabels::statusTabKeys() as $key) {
            if ($key === 'all') {
                continue;
            }
            $counts[$key] = $byStatus[$key] ?? 0;
        }

        return $counts;
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return array{labels: list<string>, values: list<int>}
     */
    private function revenueTrend(MetricsPeriod $period, array $range): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return ['labels' => [], 'values' => []];
        }

        if ($period->trendGranularity() === 'hour') {
            $rows = $this->ordersInRange($range)
                ->where('payment_status', PaymentStatus::Paid->value)
                ->selectRaw('HOUR(created_at) as bucket')
                ->selectRaw('COALESCE(SUM(total), 0) as revenue')
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $labels = [];
            $values = [];
            foreach ($rows as $row) {
                $labels[] = sprintf('%02d:00', (int) $row->bucket);
                $values[] = (int) $row->revenue;
            }

            return ['labels' => $labels, 'values' => $values];
        }

        $rows = $this->ordersInRange($range)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->selectRaw('DATE(created_at) as bucket')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = CarbonImmutable::parse((string) $row->bucket)->format('d.m');
            $values[] = (int) $row->revenue;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return array{labels: list<string>, values: list<int>}
     */
    private function ordersCountTrend(MetricsPeriod $period, array $range): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return ['labels' => [], 'values' => []];
        }

        if ($period->trendGranularity() === 'hour') {
            $rows = $this->ordersInRange($range)
                ->selectRaw('HOUR(created_at) as bucket')
                ->selectRaw('COUNT(*) as orders_count')
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $labels = [];
            $values = [];
            foreach ($rows as $row) {
                $labels[] = sprintf('%02d:00', (int) $row->bucket);
                $values[] = (int) $row->orders_count;
            }

            return ['labels' => $labels, 'values' => $values];
        }

        $rows = $this->ordersInRange($range)
            ->selectRaw('DATE(created_at) as bucket')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = CarbonImmutable::parse((string) $row->bucket)->format('d.m');
            $values[] = (int) $row->orders_count;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return array{labels: list<string>, values: list<int>}
     */
    private function deliveryMix(array $range): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return ['labels' => [], 'values' => []];
        }

        $rows = $this->ordersInRange($range)
            ->selectRaw('delivery_method, COUNT(*) as aggregate')
            ->groupBy('delivery_method')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $method = (string) ($row->delivery_method ?? 'unknown');
            $labels[] = match ($method) {
                DeliveryMethod::Courier->value => 'Курьер',
                DeliveryMethod::Pickup->value => 'Самовывоз',
                default => $method !== '' ? $method : 'Не указано',
            };
            $values[] = (int) $row->aggregate;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return array{labels: list<string>, values: list<int>}
     */
    private function paymentMix(array $range): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return ['labels' => [], 'values' => []];
        }

        $rows = $this->ordersInRange($range)
            ->selectRaw('payment_method, COUNT(*) as aggregate')
            ->groupBy('payment_method')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $method = (string) ($row->payment_method ?? 'unknown');
            $labels[] = match ($method) {
                PaymentMethod::Cash->value => 'Наличные',
                PaymentMethod::Card->value => 'Карта',
                PaymentMethod::Transfer->value => 'Перевод',
                default => $method !== '' ? $method : 'Не указано',
            };
            $values[] = (int) $row->aggregate;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return list<array{product_original_id: ?int, product_name: string, quantity: int, revenue: int}>
     */
    private function topProducts(array $range): array
    {
        if (! Schema::hasTable('ORD_order_items') || ! Schema::hasTable('ORD_orders')) {
            return [];
        }

        $rows = DB::table('ORD_order_items as items')
            ->join('ORD_orders as orders', 'orders.id', '=', 'items.order_id')
            ->whereBetween('orders.created_at', [$range['from'], $range['to']])
            ->selectRaw('items.product_original_id as product_original_id')
            ->selectRaw('items.product_name as product_name')
            ->selectRaw('COALESCE(SUM(items.quantity), 0) as quantity')
            ->selectRaw('COALESCE(SUM(items.row_total), 0) as revenue')
            ->groupBy('items.product_original_id', 'items.product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return $rows->map(fn ($row): array => [
            'product_original_id' => $row->product_original_id !== null ? (int) $row->product_original_id : null,
            'product_name' => (string) $row->product_name,
            'quantity' => (int) $row->quantity,
            'revenue' => (int) $row->revenue,
        ])->all();
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return list<array{client_id: int, client_name: string, orders_count: int, revenue: int}>
     */
    private function topClients(array $range): array
    {
        if (! Schema::hasTable('reporting_client_order_facts') || ! Schema::hasTable('UR_clients')) {
            return [];
        }

        $rows = ReportingClientOrderFact::query()
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->selectRaw('client_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->groupBy('client_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $clientNames = UR_Client::query()
            ->whereIn('id', $rows->pluck('client_id'))
            ->pluck('name', 'id');

        return $rows->map(fn ($row): array => [
            'client_id' => (int) $row->client_id,
            'client_name' => (string) ($clientNames[$row->client_id] ?? 'Клиент #'.$row->client_id),
            'orders_count' => (int) $row->orders_count,
            'revenue' => (int) $row->revenue,
        ])->all();
    }

    /**
     * @return list<array{
     *     id: string,
     *     created_at: string,
     *     status: string,
     *     total: int,
     *     customer_name: string,
     *     client_id: ?int
     * }>
     */
    private function recentOrders(): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return [];
        }

        return ORD_Order::query()
            ->orderByDesc('created_at')
            ->limit(15)
            ->get(['id', 'created_at', 'status', 'total', 'customer_name', 'client_id'])
            ->map(fn (ORD_Order $order): array => [
                'id' => $order->id,
                'created_at' => $order->created_at?->toIso8601String() ?? '',
                'status' => (string) $order->status,
                'total' => (int) $order->total,
                'customer_name' => (string) $order->customer_name,
                'client_id' => $order->client_id !== null ? (int) $order->client_id : null,
            ])
            ->all();
    }

    /**
     * @return array{active_sessions: int, cart_lines: int, checkout_drafts: int}
     */
    private function shoppingFunnel(): array
    {
        if (! Schema::hasTable('SHP_shopping_sessions')) {
            return ['active_sessions' => 0, 'cart_lines' => 0, 'checkout_drafts' => 0];
        }

        $activeSessionIds = DB::table('SHP_shopping_sessions')
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->pluck('id');

        $cartLines = 0;
        if (Schema::hasTable('SHP_shopping_cart_lines') && $activeSessionIds->isNotEmpty()) {
            $cartLines = (int) DB::table('SHP_shopping_cart_lines')
                ->whereIn('shopping_session_id', $activeSessionIds)
                ->count();
        }

        $checkoutDrafts = 0;
        if (Schema::hasTable('SHP_shopping_checkout_drafts') && $activeSessionIds->isNotEmpty()) {
            $checkoutDrafts = (int) DB::table('SHP_shopping_checkout_drafts')
                ->whereIn('shopping_session_id', $activeSessionIds)
                ->count();
        }

        $activeWithCart = 0;
        if (Schema::hasTable('SHP_shopping_cart_lines')) {
            $activeWithCart = (int) DB::table('SHP_shopping_sessions as sessions')
                ->where(function (Builder $query): void {
                    $query->whereNull('sessions.expires_at')
                        ->orWhere('sessions.expires_at', '>', now());
                })
                ->whereExists(function (Builder $query): void {
                    $query->selectRaw('1')
                        ->from('SHP_shopping_cart_lines as lines')
                        ->whereColumn('lines.shopping_session_id', 'sessions.id');
                })
                ->count();
        }

        return [
            'active_sessions' => $activeWithCart,
            'cart_lines' => $cartLines,
            'checkout_drafts' => $checkoutDrafts,
        ];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     * @return array{registered_orders: int, guest_orders: int, yandex_orders: int, site_orders: int}
     */
    private function channelStats(array $range): array
    {
        if (! Schema::hasTable('ORD_orders')) {
            return [
                'registered_orders' => 0,
                'guest_orders' => 0,
                'yandex_orders' => 0,
                'site_orders' => 0,
            ];
        }

        $guestOrders = (int) $this->ordersInRange($range)->whereNull('client_id')->count();
        $registeredOrders = (int) $this->ordersInRange($range)->whereNotNull('client_id')->count();

        $yandexOrders = $this->countYandexOrdersInRange($range);

        return [
            'registered_orders' => $registeredOrders,
            'guest_orders' => $guestOrders,
            'yandex_orders' => $yandexOrders,
            'site_orders' => max(0, $registeredOrders + $guestOrders - $yandexOrders),
        ];
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     */
    private function countYandexOrdersInRange(array $range): int
    {
        $query = $this->ordersInRange($range);

        if (Schema::hasColumn('ORD_orders', 'source')) {
            return (int) (clone $query)
                ->where('source', OrderSource::YandexFood->value)
                ->count();
        }

        if (! Schema::hasTable('yandex_food_order_meta')) {
            return 0;
        }

        return (int) DB::table('yandex_food_order_meta as meta')
            ->join('ORD_orders as orders', 'orders.id', '=', 'meta.order_id')
            ->whereBetween('orders.created_at', [$range['from'], $range['to']])
            ->count();
    }

    /**
     * @param  array{from: CarbonImmutable, to: CarbonImmutable}  $range
     */
    private function ordersInRange(array $range): Builder
    {
        return DB::table('ORD_orders')
            ->whereBetween('created_at', [$range['from'], $range['to']]);
    }

    private function cacheKey(string $scope, MetricsPeriod $period): string
    {
        return sprintf(
            'business_metrics:%s:%s:%s',
            $scope,
            $period->value,
            CarbonImmutable::now()->format('Y-m-d-H'),
        );
    }
}
