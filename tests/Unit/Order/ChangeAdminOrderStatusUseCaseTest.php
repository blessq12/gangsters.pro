<?php

namespace Tests\Unit\Order;

use App\Application\Order\Command\ChangeAdminOrderStatusUseCase;
use App\Application\Order\Exception\AdminOrderTransitionException;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Event\OrderStatusChanged;
use App\Domain\Order\Repository\OrderRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class ChangeAdminOrderStatusUseCaseTest extends TestCase
{
    public function test_confirms_a_new_order(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $repository = $this->createMock(OrderRepository::class);
        $repository->method('findById')->with(42)->willReturn($this->order(OrderStatus::New->value));
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $saved): bool {
                return $saved->status() === OrderStatus::Confirmed->value;
            }));

        $updated = (new ChangeAdminOrderStatusUseCase($repository))->execute(42, OrderStatus::Confirmed->value);

        $this->assertSame(OrderStatus::Confirmed->value, $updated->status());
        Event::assertDispatched(OrderStatusChanged::class, function (OrderStatusChanged $event): bool {
            return $event->source() === 'backoffice'
                && $event->newStatus() === OrderStatus::Confirmed->value;
        });
    }

    public function test_rejects_an_illegal_transition(): void
    {
        $repository = $this->createMock(OrderRepository::class);
        $repository->method('findById')->willReturn($this->order(OrderStatus::Preparing->value));
        $repository->expects($this->never())->method('save');

        $this->expectException(AdminOrderTransitionException::class);

        (new ChangeAdminOrderStatusUseCase($repository))->execute(42, OrderStatus::Confirmed->value);
    }

    public function test_stores_the_cancel_reason(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $repository = $this->createMock(OrderRepository::class);
        $repository->method('findById')->willReturn($this->order(OrderStatus::Preorder->value));
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $saved): bool {
                return $saved->status() === OrderStatus::Cancelled->value
                    && ($saved->delivery()['cancel_reason'] ?? null) === 'нет связи';
            }));

        (new ChangeAdminOrderStatusUseCase($repository))->execute(42, OrderStatus::Cancelled->value, 'нет связи');
    }

    private function order(string $status): Order
    {
        return Order::restore(
            id: 42,
            source: 'site',
            checkoutId: 'chk-1',
            partnerCode: null,
            externalOrderId: null,
            status: $status,
            cart: ['lines' => [['product_name' => 'Ролл', 'quantity' => 1, 'line_total_rubles' => 100]]],
            client: ['name' => 'Анна', 'phone' => '+7'],
            delivery: ['method' => 'courier'],
            payment: ['method' => 'cash'],
            createdAt: new DateTimeImmutable('2026-11-07 12:00:00'),
        );
    }
}
