<?php

namespace App\Application\Promotion\DTO;

final readonly class ResolvePromotionsDto
{
    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     */
    public function __construct(
        public array $lines,
        public string $deliveryMethod,
        public ?string $deliveryZoneId,
        public int $currentTotalKopecks,
        public ?\DateTimeInterface $scheduledAt,
    ) {}
}
