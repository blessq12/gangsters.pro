<?php

namespace App\Application\YandexFood\DTO;

final readonly class YandexUpdateOrderRequestDto
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $id,
        public array $payload,
    ) {
    }
}
