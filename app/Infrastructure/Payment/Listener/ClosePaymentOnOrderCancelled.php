<?php

namespace App\Infrastructure\Payment\Listener;

use App\Domain\Core\Order\Event\OrderCancelled;
use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Enum\PaymentStatus;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;

/**
 * Заказ отменён → pending отменяется, paid возвращается. Висящих pending не оставляем.
 */
final class ClosePaymentOnOrderCancelled
{
    public function __construct(
        private readonly PaymentRepository $payments,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        $payment = $this->payments->findByOrderId($event->orderId);
        if ($payment === null) {
            return;
        }

        match ($payment->status()) {
            PaymentStatus::Pending => $this->cancelPending($payment),
            PaymentStatus::Paid => $this->refundPaid($payment),
            PaymentStatus::Refunded, PaymentStatus::Cancelled => null,
        };
    }

    private function cancelPending(Payment $payment): void
    {
        $payment->cancel();
        $this->payments->save($payment);

        foreach ($payment->pullDomainEvents() as $domainEvent) {
            event($domainEvent);
        }
    }

    private function refundPaid(Payment $payment): void
    {
        $payment->refund();
        $this->payments->save($payment);

        foreach ($payment->pullDomainEvents() as $domainEvent) {
            event($domainEvent);
        }
    }
}
