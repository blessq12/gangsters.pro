<?php

namespace Tests\Unit\Order;

use App\Application\Order\Command\ChangeOrderStatusFromFrontpadUseCase;
use App\Application\Order\Exception\OrderNotFoundForFrontpad;
use App\Application\Order\Exception\UnknownFrontpadStatus;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Event\OrderStatusChanged;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Integration\Frontpad\FrontpadStatusMapper;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class ChangeOrderStatusFromFrontpadUseCaseTest extends TestCase
{
    public function test_throws_when_frontpad_status_unknown(): void
    {
        $useCase = new ChangeOrderStatusFromFrontpadUseCase(
            $this->createMock(OrderRepository::class),
            new FrontpadStatusMapper(),
        );

        $this->expectException(UnknownFrontpadStatus::class);

        $useCase->handle('fp-1', 99, null);
    }

    public function test_throws_when_order_not_found_by_frontpad_id(): void
    {
        $repository = $this->createMock(OrderRepository::class);
        $repository->method('findByFrontpadOrderId')
            ->with('missing-fp')
            ->willReturn(null);

        $useCase = new ChangeOrderStatusFromFrontpadUseCase(
            $repository,
            new FrontpadStatusMapper(),
        );

        $this->expectException(OrderNotFoundForFrontpad::class);

        $useCase->handle('missing-fp', 3, null);
    }

    public function test_does_not_save_or_dispatch_when_status_unchanged(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = $this->sampleOrder(OrderStatus::Preparing->value, 'fp-same');

        $repository = $this->createMock(OrderRepository::class);
        $repository->method('findByFrontpadOrderId')
            ->with('fp-same')
            ->willReturn($order);
        $repository->expects($this->never())->method('save');

        $useCase = new ChangeOrderStatusFromFrontpadUseCase(
            $repository,
            new FrontpadStatusMapper(),
        );

        $useCase->handle('fp-same', 3, null);

        Event::assertNotDispatched(OrderStatusChanged::class);
    }

    public function test_saves_order_and_dispatches_event_on_status_change(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $changedAt = new DateTimeImmutable('2026-10-19 15:00:00');
        $order = $this->sampleOrder(OrderStatus::New->value, 'fp-change');

        $repository = $this->createMock(OrderRepository::class);
        $repository->method('findByFrontpadOrderId')
            ->with('fp-change')
            ->willReturn($order);
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $saved): bool {
                return $saved->id() === 42
                    && $saved->status() === OrderStatus::Preparing->value;
            }));

        $useCase = new ChangeOrderStatusFromFrontpadUseCase(
            $repository,
            new FrontpadStatusMapper(),
        );

        $useCase->handle('fp-change', 3, $changedAt);

        Event::assertDispatched(OrderStatusChanged::class, function (OrderStatusChanged $event) use ($changedAt): bool {
            return $event->orderId() === 42
                && $event->previousStatus() === OrderStatus::New->value
                && $event->newStatus() === OrderStatus::Preparing->value
                && $event->source() === 'frontpad'
                && $event->changedAt() === $changedAt;
        });
    }

    private function sampleOrder(string $status, string $frontpadOrderId): Order
    {
        return Order::restore(
            id: 42,
            source: 'site',
            checkoutId: 'chk-1',
            partnerCode: null,
            externalOrderId: null,
            status: $status,
            cart: ['lines' => [['line_total_rubles' => 100]]],
            client: [],
            delivery: [],
            payment: [],
            createdAt: new DateTimeImmutable('2026-10-19 12:00:00'),
            frontpadOrderId: $frontpadOrderId,
        );
    }
}
