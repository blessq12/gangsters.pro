<?php

namespace App\Application\Order\DTO;

final readonly class ChangeOrderStatusDto
{
    public function __construct(
        public int $orderId,
        public string $targetStatus,
        public ?string $actorRole = null,
    ) {}
}
