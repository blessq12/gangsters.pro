<?php

namespace App\Application\YandexFood\DTO;

final readonly class YandexMenuAvailabilityRequestDto
{
    public function __construct(public string $id)
    {
    }
}
