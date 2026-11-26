<?php

namespace App\Application\Branch\DTO;

final readonly class SaveBranchDto
{
    /**
     * @param  array<string, mixed>  $schedule
     */
    public function __construct(
        public ?int $id,
        public int $companyId,
        public string $name,
        public bool $isActive,
        public array $schedule,
        public ?string $city = null,
        public ?string $street = null,
        public ?string $house = null,
        public ?string $comment = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $phone = null,
    ) {}
}
