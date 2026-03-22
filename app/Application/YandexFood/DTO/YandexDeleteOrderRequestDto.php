<?php

namespace App\Application\YandexFood\DTO;

final readonly class YandexDeleteOrderRequestDto
{
    public function __construct(
        public string $orderId,
        public string $routeId,
    ) {
    }
}
