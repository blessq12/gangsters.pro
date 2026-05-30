<?php

namespace App\Application\Operations\Client\DTO;

final readonly class AdminDeleteClientAddressDTO
{
    public function __construct(
        public int $clientId,
        public int $addressId,
    ) {
    }
}
