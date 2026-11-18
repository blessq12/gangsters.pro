<?php

namespace App\Application\Marketing\DTO;

final readonly class SaveDeliveryConditionsTextDto
{
    public function __construct(
        public ?int $id,
        public string $body,
        public bool $isActive,
    ) {}
}
