<?php

namespace App\Application\Order\Command;

use App\Application\Order\Exception\AdminOrderNotFound;
use App\Application\Order\Exception\AdminOrderTransitionException;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Event\OrderStatusChanged;
use App\Domain\Order\Repository\OrderRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;

final class ChangeAdminOrderStatusUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(int $orderId, string $status, ?string $reason = null): Order
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw AdminOrderNotFound::forId($orderId);
        }

        $updated = $this->transit($order, $status, $reason);
        $this->orders->save($updated);

        Event::dispatch(new OrderStatusChanged(
            orderId: $updated->id(),
            previousStatus: $order->status(),
            newStatus: $updated->status(),
            source: 'backoffice',
            changedAt: new DateTimeImmutable(),
        ));

        return $updated;
    }

    private function transit(Order $order, string $status, ?string $reason): Order
    {
        $current = $order->status();
        $method = (string) ($order->delivery()['method'] ?? '');

        if ($status === OrderStatus::Confirmed->value && in_array($current, [OrderStatus::New->value, OrderStatus::Preorder->value], true)) {
            return $order->withStatus(OrderStatus::Confirmed);
        }

        if ($status === OrderStatus::Cancelled->value && in_array($current, [OrderStatus::New->value, OrderStatus::Preorder->value], true)) {
            return $order->withCancelled((string) $reason);
        }

        if ($status === OrderStatus::Preparing->value && $current === OrderStatus::Confirmed->value) {
            return $order->withKitchenStarted(new DateTimeImmutable());
        }

        if ($status === OrderStatus::Ready->value && $current === OrderStatus::Preparing->value) {
            return $order->withStatus(OrderStatus::Ready);
        }

        if ($status === OrderStatus::Delivered->value && $current === OrderStatus::Ready->value && $method === 'pickup') {
            return $order->withStatus(OrderStatus::Delivered);
        }

        if ($status === OrderStatus::InTransit->value && $current === OrderStatus::Ready->value && $method === 'courier') {
            return $order->withStatus(OrderStatus::InTransit);
        }

        if ($status === OrderStatus::Delivered->value && $current === OrderStatus::InTransit->value) {
            return $order->withStatus(OrderStatus::Delivered);
        }

        if ($status === OrderStatus::Ready->value && $current === OrderStatus::InTransit->value) {
            return $order->withStatus(OrderStatus::Ready);
        }

        throw new AdminOrderTransitionException('Этот переход статуса заказу недоступен.');
    }
}
