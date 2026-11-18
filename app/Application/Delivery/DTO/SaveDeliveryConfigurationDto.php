<?php

namespace App\Application\Delivery\DTO;

final readonly class SaveDeliveryConfigurationDto
{
    /**
     * @param  array<string, mixed>  $kitchen
     * @param  list<array<string, mixed>>  $zones
     */
    public function __construct(
        public ?int $minOrderRubles,
        public ?int $averageDeliveryTimeMinutes,
        public array $kitchen,
        public array $zones,
    ) {}
}
