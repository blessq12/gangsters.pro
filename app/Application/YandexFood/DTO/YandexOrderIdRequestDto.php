<?php

namespace App\Application\YandexFood\DTO;

final readonly class YandexOrderIdRequestDto
{
    public function __construct(public string $id)
    {
    }
}
