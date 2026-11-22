<?php

namespace App\Domain\Core\Delivery\Event;

/**
 * Набор зон доставки филиала изменился. Несёт полный актуальный снимок зон.
 */
final readonly class DeliveryZonesChanged
{
    /**
     * @param  list<array{id: string, name: string, fee_kopecks: int, is_remote: bool}>  $zones
     */
    public function __construct(
        public int $branchId,
        public array $zones,
    ) {}
}
