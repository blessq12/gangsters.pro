<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Delivery\Event\RouteStopSettled;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderAuditRepository;
use App\Domain\Core\Order\Repository\OrderRepository;

/**
 * Delivery закрыл остановку; Order применяет оплату (если есть) и финальный статус.
 */
final class SettleOrderOnRouteStopSettled
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderAuditRepository $audits,
    ) {}

    public function handle(RouteStopSettled $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        if ($event->outcome === RouteStopSettled::OUTCOME_DELIVERED && $event->paymentMethod !== null) {
            $this->applyPaymentTerms($order, $event);
        }

        match ($event->outcome) {
            RouteStopSettled::OUTCOME_DELIVERED => $order->markDelivered(),
            RouteStopSettled::OUTCOME_WRITTEN_OFF => $order->writeOff(),
            default => throw new \InvalidArgumentException('Неизвестный исход остановки: '.$event->outcome),
        };

        $this->orders->save($order);
    }

    private function applyPaymentTerms(Order $order, RouteStopSettled $event): void
    {
        $method = (string) $event->paymentMethod;
        $before = [
            'payment_method' => $order->paymentMethod(),
            'change_from_rubles' => $order->changeFromRubles(),
        ];

        $changeFrom = $method === 'cash' ? $order->changeFromRubles() : null;
        $order->replacePaymentTerms($method, $changeFrom);

        $after = [
            'payment_method' => $order->paymentMethod(),
            'change_from_rubles' => $order->changeFromRubles(),
        ];

        if ($before !== $after) {
            $this->audits->add(
                (int) $order->id(),
                $event->authorEmployeeId,
                [['field' => 'payment', 'old' => $before, 'new' => $after]],
            );
        }
    }
}
