<?php

namespace App\Application\YandexFood\DTO;

final readonly class YandexCreateOrderRequestDto
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload)
    {
    }
}
