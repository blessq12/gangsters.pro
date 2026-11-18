<?php

namespace App\Application\Company\DTO;

final readonly class SaveKitchenDto
{
    public function __construct(
        public ?int $id,
        public int $branchId,
        public ?string $city,
        public ?string $street,
        public ?string $house,
        public ?string $comment,
        public ?float $latitude,
        public ?float $longitude,
    ) {}
}
