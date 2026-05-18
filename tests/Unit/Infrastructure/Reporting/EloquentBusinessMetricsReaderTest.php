<?php

namespace Tests\Unit\Infrastructure\Reporting;

use App\Application\Reporting\ValueObject\MetricsPeriod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Reporting\Query\EloquentBusinessMetricsReader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EloquentBusinessMetricsReaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->databaseTableExists('ORD_orders')) {
            $this->markTestSkipped('Нет таблицы ORD_orders для business metrics теста.');
        }

        Cache::flush();
    }

    public function test_today_snapshot_counts_paid_revenue(): void
    {
        $orderId = (string) Str::uuid();

        ORD_Order::query()->create([
            'id' => $orderId,
            'client_id' => null,
            'status' => 'delivered',
            'subtotal' => 150_000,
            'discount_total' => 0,
            'total' => 150_000,
            'customer_name' => 'Metrics Guest',
            'customer_phone' => '+79990001122',
            'customer_email' => null,
            'customer_address' => null,
            'delivery_method' => 'pickup',
            'delivery_address' => null,
            'delivery_comment' => null,
            'payment_method' => 'cash',
            'payment_external_id' => null,
            'payment_status' => PaymentStatus::Paid->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reader = new EloquentBusinessMetricsReader;
        $snapshot = $reader->build(MetricsPeriod::Today);

        $this->assertGreaterThanOrEqual(150_000, $snapshot->revenueKpi['paid_revenue']);
        $this->assertGreaterThanOrEqual(1, $snapshot->revenueKpi['orders_count']);

        ORD_Order::query()->whereKey($orderId)->delete();
    }

    private function databaseTableExists(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    public function test_metrics_period_resolves_ranges(): void
    {
        $period = MetricsPeriod::SevenDays;
        $range = $period->currentRange();
        $previous = $period->previousRange();

        $this->assertTrue($range['from']->lessThanOrEqualTo($range['to']));
        $this->assertTrue($previous['to']->lessThan($range['from']));
    }
}
