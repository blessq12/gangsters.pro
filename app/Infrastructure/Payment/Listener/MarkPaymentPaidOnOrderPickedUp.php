<?php

namespace App\Infrastructure\Payment\Listener;

use App\Domain\Core\Order\Event\OrderPickedUp;
use App\Domain\Supporting\Payment\Enum\PaymentStatus;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;

/**
 * Самовывоз выдан → pending-платёж становится paid.
 */
final class MarkPaymentPaidOnOrderPickedUp
{
    public function __construct(
        private readonly PaymentRepository $payments,
    ) {}

    public function handle(OrderPickedUp $event): void
    {
        $payment = $this->payments->findByOrderId($event->orderId);
        if ($payment === null) {
            return;
        }

        if ($payment->status() !== PaymentStatus::Pending) {
            return;
        }

        $payment->markPaid();
        $this->payments->save($payment);

        foreach ($payment->pullDomainEvents() as $domainEvent) {
            event($domainEvent);
        }
    }
}
