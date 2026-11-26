<?php

namespace App\Application\Branch\DTO;

final readonly class SaveDeliveryConfigurationDto
{
    /**
     * @param  list<array<string, mixed>>|null  $zones  null = не трогать зоны филиала
     */
    public function __construct(
        public ?int $minOrderRubles,
        public ?int $averageDeliveryTimeMinutes,
        public int $branchId,
        public ?array $zones = null,
    ) {}
}
