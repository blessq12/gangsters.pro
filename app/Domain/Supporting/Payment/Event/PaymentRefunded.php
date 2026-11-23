<?php

namespace App\Domain\Supporting\Payment\Event;

use DateTimeImmutable;

final readonly class PaymentRefunded
{
    public function __construct(
        public int $paymentId,
        public int $orderId,
        public DateTimeImmutable $refundedAt,
    ) {}
}
