<?php

namespace App\Application\Delivery\DTO;

final readonly class SaveDeliveryConfigurationDto
{
    /**
     * @param  list<array<string, mixed>>  $zones
     */
    public function __construct(
        public ?int $minOrderRubles,
        public ?int $averageDeliveryTimeMinutes,
        public ?int $kitchenId,
        public array $zones,
    ) {}
}
