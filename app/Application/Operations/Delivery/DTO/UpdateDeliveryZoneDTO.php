<?php

namespace App\Application\Operations\Delivery\DTO;

final readonly class UpdateDeliveryZoneDTO
{
    /**
     * @param  array<string, mixed>|null  $deliveryZoneGeojson
     */
    public function __construct(
        public ?array $deliveryZoneGeojson,
        public ?float $kitchenLatitude,
        public ?float $kitchenLongitude,
    ) {
    }
}
