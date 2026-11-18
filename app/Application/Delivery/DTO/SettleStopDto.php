<?php

namespace App\Application\Delivery\DTO;

final readonly class SettleStopDto
{
    public function __construct(
        public int $sheetId,
        public int $orderId,
        public string $outcome,
        public ?string $reason = null,
    ) {}
}
