<?php

namespace Tests\Domain\Order;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Event\OrderPacked;
use App\Domain\Core\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderPackedTransitionTest extends TestCase
{
    public function test_mark_packed_из_ready(): void
    {
        $order = $this->orderAt(OrderStatus::Ready, pickup: true);
        $order->markPacked();

        $this->assertSame(OrderStatus::Packed, $order->status());
        $events = $order->pullDomainEvents();
        $this->assertInstanceOf(OrderPacked::class, $events[0]);
    }

    public function test_самовывоз_только_из_packed(): void
    {
        $order = $this->orderAt(OrderStatus::Ready, pickup: true);

        $this->expectException(\InvalidArgumentException::class);
        $order->markDeliveredPickup();
    }

    public function test_самовывоз_из_packed_ок(): void
    {
        $order = $this->orderAt(OrderStatus::Packed, pickup: true);
        $order->markDeliveredPickup();

        $this->assertSame(OrderStatus::Delivered, $order->status());
    }

    public function test_маршрут_только_из_packed(): void
    {
        $order = $this->orderAt(OrderStatus::Ready, pickup: false);
        $this->assertFalse($order->canBeAssignedToRoute());

        $order->markPacked();
        $this->assertTrue($order->canBeAssignedToRoute());
        $order->assignToRoute();
        $this->assertSame(OrderStatus::AssignedToRoute, $order->status());
    }

    private function orderAt(OrderStatus $status, bool $pickup): Order
    {
        $delivery = $pickup
            ? OrderDeliveryInfo::pickup()
            : OrderDeliveryInfo::courier(
                OrderDeliveryAddress::create('Тестовая', '1', lat: 55.0, lng: 37.0),
                'zone-1',
                10000,
            );

        return Order::restore(
            id: 42,
            source: 'backoffice',
            checkoutId: null,
            partnerCode: null,
            externalOrderId: null,
            status: $status,
            lines: [new OrderLine(1, 'Ролл', 50000, 1, OrderLineType::Regular)],
            client: new OrderClientSnapshot(1, 'Клиент', '+79990001122'),
            delivery: $delivery,
            createdAt: new DateTimeImmutable('2026-11-25 12:00:00'),
            branchId: 2,
            paymentId: 1,
            paymentMethod: 'cash',
            changeFromRubles: null,
        );
    }
}
