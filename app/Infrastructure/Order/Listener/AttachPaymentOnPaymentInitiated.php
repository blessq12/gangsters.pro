<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Payment\Event\PaymentInitiated;

/**
 * Payment публикует заведённый платёж; Order сохраняет только ссылку на него.
 */
final class AttachPaymentOnPaymentInitiated
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(PaymentInitiated $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $order->attachPayment($event->paymentId);
        $this->orders->save($order);
    }
}
