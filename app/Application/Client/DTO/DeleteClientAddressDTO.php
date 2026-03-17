<?php

namespace App\Application\Client\DTO;

final class DeleteClientAddressDTO
{
    public function __construct(
        public readonly int $addressId,
    ) {
    }
}

