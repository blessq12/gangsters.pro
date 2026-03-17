<?php

namespace App\Application\Client\DTO;

final class AddClientAddressDTO
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $title,
        public readonly string $street,
        public readonly string $house,
        public readonly ?string $entrance,
        public readonly ?string $apartment,
        public readonly bool $makeDefault,
    ) {
    }
}
