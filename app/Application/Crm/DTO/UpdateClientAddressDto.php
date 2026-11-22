<?php

namespace App\Application\Crm\DTO;

final readonly class UpdateClientAddressDto
{
    public function __construct(
        public string $addressId,
        public ?string $type,
        public ?string $title,
        public string $street,
        public string $house,
        public ?string $entrance,
        public ?string $floor,
        public ?string $apartment,
        public ?string $comment,
        public ?bool $makeDefault,
    ) {}
}
