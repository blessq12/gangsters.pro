<?php

namespace App\Application\Client\DTO;

final readonly class DeleteClientAddressDto
{
    public function __construct(
        public int $clientId,
        public int $addressId,
    ) {}
}
