<?php

namespace App\Application\Order\DTO;

use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use DateTimeImmutable;

final readonly class CreateOrderDto
{
    public function __construct(
        public string $checkoutId,
        public OrderCartSnapshot $cart,
        public OrderClientSnapshot $client,
        public OrderDeliverySnapshot $delivery,
        public OrderPaymentSnapshot $payment,
        public DateTimeImmutable $createdAt,
    ) {}
}
