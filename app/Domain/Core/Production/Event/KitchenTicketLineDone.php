<?php

namespace App\Domain\Core\Production\Event;

use DateTimeImmutable;

/**
 * Позиция кухонного тикета отмечена готовой (свободный пул поваров).
 */
final readonly class KitchenTicketLineDone
{
    public function __construct(
        public int $orderId,
        public int $ticketId,
        public int $lineId,
        public int $employeeId,
        public DateTimeImmutable $doneAt,
    ) {}
}
