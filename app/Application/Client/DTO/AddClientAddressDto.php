<?php

namespace App\Application\Client\DTO;

final readonly class AddClientAddressDto
{
    public function __construct(
        public int $clientId,
        public ?string $type,
        public ?string $title,
        public string $street,
        public string $house,
        public ?string $entrance,
        public ?string $apartment,
        public ?string $comment,
        public bool $makeDefault,
    ) {}
}
