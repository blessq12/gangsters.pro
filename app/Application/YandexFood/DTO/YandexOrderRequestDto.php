<?php

namespace App\Application\YandexFood\DTO;

class YandexOrderRequestDto
{
    public function __construct(
        public readonly array $payload,
    ) {
    }
}

