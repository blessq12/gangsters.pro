<?php

namespace App\Domain\Core\Delivery\Event;

final readonly class RouteStopSettled
{
    public const OUTCOME_DELIVERED = 'delivered';

    public const OUTCOME_WRITTEN_OFF = 'written_off';

    /**
     * @param  self::OUTCOME_*  $outcome
     */
    public function __construct(
        public int $routeSheetId,
        public int $orderId,
        public string $outcome,
        public ?string $reason = null,
    ) {}
}
