<?php

namespace App\Domain\Production\Enum;

/** Статус кухонного тикета. */
enum KitchenTicketStatus: string
{
    case Queued = 'queued';
    case Started = 'started';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
