<?php

namespace App\Domain\Payment\Event;

final readonly class PaymentInitiated
{
    public function __construct(
        public int $paymentId,
        public int $orderId,
    ) {}
}
