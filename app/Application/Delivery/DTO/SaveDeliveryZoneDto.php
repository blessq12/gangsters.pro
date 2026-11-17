<?php

namespace App\Application\Delivery\DTO;

final readonly class SaveDeliveryZoneDto
{
    /**
     * @param  array<string, mixed>|null  $geometry
     */
    public function __construct(
        public ?string $id,
        public string $name,
        public int $feeRubles,
        public bool $isRemote,
        public ?array $geometry,
    ) {}
}
