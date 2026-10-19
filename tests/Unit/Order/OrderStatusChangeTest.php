<?php

namespace Tests\Unit\Order;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderStatusChangeTest extends TestCase
{
    public function test_with_status_returns_new_instance_keeping_id(): void
    {
        $order = Order::restore(
            id: 42,
            source: 'site',
            checkoutId: 'chk-1',
            partnerCode: null,
            externalOrderId: null,
            status: OrderStatus::New->value,
            cart: ['lines' => [['line_total_rubles' => 100]]],
            client: [],
            delivery: [],
            payment: [],
            createdAt: new DateTimeImmutable('2026-10-19 12:00:00'),
            frontpadOrderId: null,
        );

        $updated = $order->withStatus(OrderStatus::Preparing);

        $this->assertSame(42, $updated->id());
        $this->assertSame(OrderStatus::Preparing->value, $updated->status());
        $this->assertSame(OrderStatus::New->value, $order->status());
    }

    public function test_with_frontpad_order_id(): void
    {
        $order = Order::fromCheckoutSnapshot(
            clientRequestId: 'chk-2',
            cart: ['lines' => [['line_total_rubles' => 50]]],
            client: [],
            delivery: [],
            payment: [],
            createdAt: new DateTimeImmutable('2026-10-19 12:00:00'),
        );
        $order->assignId(7);

        $linked = $order->withFrontpadOrderId('12345678');

        $this->assertSame('12345678', $linked->frontpadOrderId());
        $this->assertNull($order->frontpadOrderId());
    }
}
