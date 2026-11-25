<?php

namespace App\Domain\Core\Production\Enum;

enum KitchenTicketLineStatus: string
{
    case Queued = 'queued';
    case Done = 'done';
    case Cancelled = 'cancelled';
}
