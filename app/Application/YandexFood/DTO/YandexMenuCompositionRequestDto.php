<?php

namespace App\Application\YandexFood\DTO;

final readonly class YandexMenuCompositionRequestDto
{
    public function __construct(public string $id)
    {
    }
}
