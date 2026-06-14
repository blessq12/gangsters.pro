<?php

namespace App\Application\Order\DTO;

final readonly class ListClientOrdersDto
{
    public function __construct(
        public int $clientId,
    ) {}
}
