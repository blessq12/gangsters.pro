<?php

namespace App\Application\Client\DTO;

final readonly class ToggleClientFavoriteDto
{
    public function __construct(
        public int $clientId,
        public int $productId,
        public ?string $productName,
        public ?float $priceRub,
        public ?string $weight,
    ) {}
}
