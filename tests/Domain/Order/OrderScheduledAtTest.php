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
    public function testComposeСБудущимScheduledAtДаётPreorder(): void
    {
        $created = new DateTimeImmutable('2026-11-25 12:00:00');
        $scheduled = new DateTimeImmutable('2026-11-25 18:00:00');

        $order = Order::compose(
            checkoutId: 'req-1',
            client: new OrderClientSnapshot(1, 'Анна', '+79990000000'),
            delivery: OrderDeliveryInfo::pickup(),
            lines: [new OrderLine(1, 'Ролл', 50000, 1, OrderLineType::Regular)],
            createdAt: $created,
            branchId: 2,
            scheduledAt: $scheduled,
            source: 'backoffice',
        );

        $this->assertSame(OrderStatus::Preorder, $order->status());
        $this->assertEquals($scheduled, $order->scheduledAt());
    }
}
