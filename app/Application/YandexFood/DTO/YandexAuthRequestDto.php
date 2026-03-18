<?php

namespace App\Application\YandexFood\DTO;

class YandexAuthRequestDto
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
    ) {
    }
}

