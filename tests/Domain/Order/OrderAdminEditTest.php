<?php

namespace Tests\Domain\Order;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Event\OrderLinesChanged;
use App\Domain\Core\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderAdminEditTest extends TestCase
{
    public function testПравкаКлиентаИОплатыВЛюбомСтатусе(): void
    {
        $order = $this->order(OrderStatus::InTransit);
        $order->replaceClient(new OrderClientSnapshot(2, 'Борис', '+79991112233'));
        $order->replacePaymentTerms('card_courier', 5000);

        $this->assertSame('Борис', $order->client()->name());
        $this->assertSame('card_courier', $order->paymentMethod());
        $this->assertSame(5000, $order->changeFromRubles());
    }

    public function testЗаменаЛинийПишетOrderLinesChanged(): void
    {
        $order = $this->order(OrderStatus::Preparing);
        $order->replaceLines([
            new OrderLine(9, 'Новый ролл', 60000, 2, OrderLineType::Regular),
        ]);

        $this->assertSame(9, $order->lines()[0]->productId());
        $events = $order->pullDomainEvents();
        $this->assertInstanceOf(OrderLinesChanged::class, $events[0]);
        $this->assertSame(42, $events[0]->orderId);
    }

    public function testСинкСтатусаПоКухне(): void
    {
        $order = $this->order(OrderStatus::Packed);
        $order->syncStatusFromKitchenProgress(allDone: false, allPacked: false);
        $this->assertSame(OrderStatus::Preparing, $order->status());

        $order->syncStatusFromKitchenProgress(allDone: true, allPacked: false);
        $this->assertSame(OrderStatus::Ready, $order->status());

        $order->syncStatusFromKitchenProgress(allDone: true, allPacked: true);
        $this->assertSame(OrderStatus::Packed, $order->status());
    }

    public function testПравкаСлотаПредзаказа(): void
    {
        $order = $this->order(OrderStatus::Confirmed);
        $slot = new DateTimeImmutable('2026-12-01 18:30:00');
        $order->replaceScheduledAt($slot);

        $this->assertSame($slot->format(DATE_ATOM), $order->scheduledAt()?->format(DATE_ATOM));

        $order->replaceScheduledAt(null);
        $this->assertNull($order->scheduledAt());
    }

    private function order(OrderStatus $status): Order
    {
        return Order::restore(
            id: 42,
            source: 'backoffice',
            checkoutId: null,
            partnerCode: null,
            externalOrderId: null,
            status: $status,
            lines: [new OrderLine(1, 'Ролл', 50000, 1, OrderLineType::Regular)],
            client: new OrderClientSnapshot(1, 'Анна', '+79990000000'),
            delivery: OrderDeliveryInfo::pickup(),
            createdAt: new DateTimeImmutable('2026-11-25 12:00:00'),
            branchId: 2,
            paymentId: 1,
            paymentMethod: 'cash',
            changeFromRubles: null,
        );
    }
}
