<?php

namespace App\Application\Operations\Delivery\DTO;

final readonly class UpdateDeliverySettingsDto
{
    /**
     * @param  array<string, mixed>|null  $deliveryZoneGeojson
     */
    public function __construct(
        public ?array $deliveryZoneGeojson,
        public ?float $kitchenLatitude,
        public ?float $kitchenLongitude,
        public ?string $deliveryHours,
        public ?int $minOrderAmountKopecks,
        public ?int $deliveryFeeKopecks,
        public ?int $averageDeliveryTimeMinutes,
    ) {}
}
