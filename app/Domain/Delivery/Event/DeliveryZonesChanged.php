<?php

namespace App\Domain\Delivery\Event;

/**
 * Набор зон доставки изменился. Несёт полный актуальный снимок зон.
 */
final readonly class DeliveryZonesChanged
{
    /**
     * @param  list<array{id: string, name: string, fee_kopecks: int, is_remote: bool}>  $zones
     */
    public function __construct(
        public array $zones,
    ) {}
}
