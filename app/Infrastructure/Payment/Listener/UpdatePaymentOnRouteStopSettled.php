<?php

namespace App\Infrastructure\Payment\Listener;

use App\Domain\Core\Delivery\Event\RouteStopSettled;
use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Enum\PaymentStatus;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;

/**
 * Delivery закрыл остановку → Payment обновляет статус поручения.
 */
final class UpdatePaymentOnRouteStopSettled
{
    public function __construct(
        private readonly PaymentRepository $payments,
    ) {}

    public function handle(RouteStopSettled $event): void
    {
        $payment = $this->payments->findByOrderId($event->orderId);
        if ($payment === null) {
            return;
        }

        match ($event->outcome) {
            RouteStopSettled::OUTCOME_DELIVERED => $this->markDeliveredPaid($payment),
            RouteStopSettled::OUTCOME_WRITTEN_OFF => $this->markWrittenOff($payment),
            default => throw new \InvalidArgumentException(
                'Неизвестный исход остановки: '.$event->outcome,
            ),
        };
    }

    private function markDeliveredPaid(Payment $payment): void
    {
        if ($payment->status() !== PaymentStatus::Pending) {
            return;
        }

        $payment->markPaid();
        $this->payments->save($payment);

        foreach ($payment->pullDomainEvents() as $domainEvent) {
            event($domainEvent);
        }
    }

    private function markWrittenOff(Payment $payment): void
    {
        match ($payment->status()) {
            PaymentStatus::Pending => $payment->cancel(),
            PaymentStatus::Paid => $payment->refund(),
            PaymentStatus::Refunded, PaymentStatus::Cancelled => null,
        };

        $events = $payment->pullDomainEvents();
        if ($events === []) {
            return;
        }

        $this->payments->save($payment);

        foreach ($events as $domainEvent) {
            event($domainEvent);
        }
    }
}
