<?php

namespace App\Infrastructure\Production\Mapper;

use App\Domain\Production\Entity\KitchenTicket;
use App\Domain\Production\Enum\KitchenTicketStatus;
use App\Infrastructure\Production\Model\KIT_Ticket;
use DateTimeImmutable;

final class KitchenTicketMapper
{
    public function toDomain(KIT_Ticket $row): KitchenTicket
    {
        $status = KitchenTicketStatus::tryFrom((string) $row->status)
            ?? KitchenTicketStatus::Queued;

        return KitchenTicket::restore(
            id: (int) $row->id,
            orderId: (int) $row->order_id,
            status: $status,
            startedAt: $row->started_at !== null
                ? DateTimeImmutable::createFromInterface($row->started_at)
                : null,
            completedAt: $row->completed_at !== null
                ? DateTimeImmutable::createFromInterface($row->completed_at)
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(KitchenTicket $ticket): array
    {
        return [
            'order_id' => $ticket->orderId(),
            'status' => $ticket->status()->value,
            'started_at' => $ticket->startedAt(),
            'completed_at' => $ticket->completedAt(),
        ];
    }
}
