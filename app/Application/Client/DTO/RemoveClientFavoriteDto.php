<?php

namespace App\Application\Client\DTO;

final readonly class RemoveClientFavoriteDto
{
    public function __construct(
        public int $clientId,
        public int $productId,
    ) {}
}
