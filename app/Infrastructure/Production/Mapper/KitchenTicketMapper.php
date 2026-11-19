<?php

namespace App\Infrastructure\Production\Mapper;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use App\Infrastructure\Production\Model\KIT_Ticket;
use App\Infrastructure\Production\Model\KIT_TicketLine;
use DateTimeImmutable;

final class KitchenTicketMapper
{
    public function toDomain(KIT_Ticket $row): KitchenTicket
    {
        $status = KitchenTicketStatus::tryFrom((string) $row->status)
            ?? KitchenTicketStatus::Queued;

        $lines = [];
        $lineRows = $row->relationLoaded('lines')
            ? $row->lines
            : $row->lines()->get();

        foreach ($lineRows as $lineRow) {
            if (! $lineRow instanceof KIT_TicketLine) {
                continue;
            }

            $lines[] = new KitchenTicketLine(
                productId: (int) $lineRow->product_id,
                name: (string) $lineRow->name,
                quantity: (int) $lineRow->quantity,
            );
        }

        return KitchenTicket::restore(
            id: (int) $row->id,
            orderId: (int) $row->order_id,
            kitchenId: (int) $row->kitchen_id,
            lines: $lines,
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
     * @return array{ticket: array<string, mixed>, lines: list<array<string, mixed>>}
     */
    public function toPersistence(KitchenTicket $ticket): array
    {
        $ticketPayload = [
            'order_id' => $ticket->orderId(),
            'kitchen_id' => $ticket->kitchenId(),
            'status' => $ticket->status()->value,
            'started_at' => $ticket->startedAt(),
            'completed_at' => $ticket->completedAt(),
        ];

        $lines = [];
        foreach (array_values($ticket->lines()) as $index => $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'position' => $index,
            ];
        }

        return [
            'ticket' => $ticketPayload,
            'lines' => $lines,
        ];
    }
}
