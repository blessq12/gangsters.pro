<?php

namespace App\Application\Checkout\DTO;

use App\Shared\Enum\DeliveryMethod;
use App\Domain\Checkout\ValueObject\DeliveryAddress;

final readonly class SetCheckoutDeliveryDto
{
    public function __construct(
        public string $checkoutId,
        public DeliveryMethod $method,
        public ?DeliveryAddress $address = null,
        public ?string $comment = null,
        public ?string $scheduledAt = null,
    ) {}
}
