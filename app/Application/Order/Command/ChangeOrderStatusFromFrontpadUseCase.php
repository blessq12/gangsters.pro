<?php

namespace App\Application\Order\Command;

use App\Application\Order\Exception\OrderNotFoundForFrontpad;
use App\Application\Order\Exception\UnknownFrontpadStatus;
use App\Domain\Order\Event\OrderStatusChanged;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Integration\Frontpad\FrontpadStatusMapper;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;

final class ChangeOrderStatusFromFrontpadUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly FrontpadStatusMapper $statusMapper,
    ) {}

    public function handle(string $frontpadOrderId, int $frontpadStatus, ?DateTimeImmutable $changedAt): void
    {
        $mapped = $this->statusMapper->toDomain($frontpadStatus);
        if ($mapped === null) {
            throw new UnknownFrontpadStatus($frontpadStatus);
        }

        $order = $this->orders->findByFrontpadOrderId($frontpadOrderId);
        if ($order === null) {
            throw new OrderNotFoundForFrontpad($frontpadOrderId);
        }

        if ($order->status() === $mapped->value) {
            return;
        }

        $previous = $order->status();
        $updated = $order->withStatus($mapped);
        $this->orders->save($updated);

        Event::dispatch(new OrderStatusChanged(
            orderId: $updated->id(),
            previousStatus: $previous,
            newStatus: $mapped->value,
            source: 'frontpad',
            changedAt: $changedAt,
        ));
    }
}
