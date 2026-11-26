<?php

namespace Tests\Domain\Order;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderScheduledAtTest extends TestCase
{
    public function test_compose_с_будущим_scheduled_at_даёт_preorder(): void
    {
        $created = new DateTimeImmutable('2026-11-25 12:00:00');
        $scheduled = new DateTimeImmutable('2026-11-25 18:00:00');

        $order = $this->compose($created, $scheduled);

        $this->assertSame(OrderStatus::Preorder, $order->status());
        $this->assertEquals($scheduled, $order->scheduledAt());
    }

    public function test_compose_без_scheduled_at_даёт_new_asap(): void
    {
        $created = new DateTimeImmutable('2026-11-25 12:00:00');
        $order = $this->compose($created, null);

        $this->assertSame(OrderStatus::New, $order->status());
        $this->assertNull($order->scheduledAt());
    }

    public function test_compose_с_прошлым_scheduled_at_даёт_new(): void
    {
        $created = new DateTimeImmutable('2026-11-25 12:00:00');
        $scheduled = new DateTimeImmutable('2026-11-25 10:00:00');
        $order = $this->compose($created, $scheduled);

        $this->assertSame(OrderStatus::New, $order->status());
        $this->assertEquals($scheduled, $order->scheduledAt());
    }

    public function test_replace_scheduled_at_синхронизирует_preorder_и_new(): void
    {
        $created = new DateTimeImmutable('2026-11-25 12:00:00');
        $order = $this->compose($created, null);
        $this->assertSame(OrderStatus::New, $order->status());

        $slot = new DateTimeImmutable('2026-11-26 18:00:00');
        $order->replaceScheduledAt($slot);
        $this->assertSame(OrderStatus::Preorder, $order->status());
        $this->assertEquals($slot, $order->scheduledAt());

        $order->replaceScheduledAt(null);
        $this->assertSame(OrderStatus::New, $order->status());
        $this->assertNull($order->scheduledAt());
    }

    public function test_replace_scheduled_at_на_позднем_статусе_не_меняет_status(): void
    {
        $created = new DateTimeImmutable('2026-11-25 12:00:00');
        $order = $this->compose($created, null);
        $order->assignId(42);
        $order->accept();
        $this->assertSame(OrderStatus::Confirmed, $order->status());

        $slot = new DateTimeImmutable('2026-11-26 18:00:00');
        $order->replaceScheduledAt($slot);
        $this->assertSame(OrderStatus::Confirmed, $order->status());
        $this->assertEquals($slot, $order->scheduledAt());
    }

    public function test_accept_из_preorder_в_confirmed(): void
    {
        $created = new DateTimeImmutable('2026-11-25 12:00:00');
        $slot = new DateTimeImmutable('2026-11-26 18:00:00');
        $order = $this->compose($created, $slot);
        $order->assignId(7);

        $order->accept();
        $this->assertSame(OrderStatus::Confirmed, $order->status());
        $this->assertEquals($slot, $order->scheduledAt());
    }

    private function compose(DateTimeImmutable $created, ?DateTimeImmutable $scheduled): Order
    {
        return Order::compose(
            checkoutId: 'req-1',
            client: new OrderClientSnapshot(1, 'Анна', '+79990000000'),
            delivery: OrderDeliveryInfo::pickup(),
            lines: [new OrderLine(1, 'Ролл', 50000, 1, OrderLineType::Regular)],
            createdAt: $created,
            branchId: 2,
            scheduledAt: $scheduled,
            source: 'backoffice',
        );
    }
}
