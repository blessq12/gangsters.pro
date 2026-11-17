<?php

namespace App\Application\Crm\DTO;

final readonly class AddClientAddressDto
{
    public function __construct(
        public ?string $type,
        public ?string $title,
        public string $street,
        public string $house,
        public ?string $entrance,
        public ?string $floor,
        public ?string $apartment,
        public ?string $comment,
        public bool $makeDefault,
    ) {}
}
