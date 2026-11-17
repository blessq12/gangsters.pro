<?php

namespace App\Application\Crm\DTO;

final readonly class ToggleFavoriteDto
{
    public function __construct(
        public int $productId,
        public ?string $name,
        public ?int $priceKopecks,
    ) {}
}
