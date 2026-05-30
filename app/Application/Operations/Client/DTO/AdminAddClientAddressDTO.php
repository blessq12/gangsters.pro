<?php

namespace App\Application\Operations\Client\DTO;

final readonly class AdminAddClientAddressDTO
{
    public function __construct(
        public int $clientId,
        public string $type,
        public ?string $title,
        public string $street,
        public string $house,
        public ?string $entrance,
        public ?string $apartment,
        public bool $makeDefault,
    ) {
    }
}
