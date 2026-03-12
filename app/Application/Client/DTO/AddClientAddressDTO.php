<?php

namespace App\Application\Client\DTO;

final class AddClientAddressDTO
{
    public function __construct(
        public readonly int $clientId,
        public readonly string $type,
        public readonly ?string $title,
        public readonly string $street,
        public readonly string $house,
        public readonly ?string $liter,
        public readonly ?string $staircase,
        public readonly ?string $apartment,
        public readonly ?string $entranceCode,
        public readonly ?string $floor,
        public readonly ?string $comment,
        public readonly bool $makeDefault,
    ) {
    }
}

