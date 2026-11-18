<?php

namespace App\Infrastructure\Production\Repository;

use App\Domain\Production\Entity\KitchenTicket;
use App\Domain\Production\Enum\KitchenTicketStatus;
use App\Domain\Production\Repository\KitchenTicketRepository;
use App\Infrastructure\Production\Mapper\KitchenTicketMapper;
use App\Infrastructure\Production\Model\KIT_Ticket;

final class EloquentKitchenTicketRepository implements KitchenTicketRepository
{
    public function __construct(
        private readonly KitchenTicketMapper $mapper,
    ) {}

    public function findByOrderId(int $orderId): ?KitchenTicket
    {
        $row = KIT_Ticket::query()
            ->where('order_id', $orderId)
            ->first();

        return $row instanceof KIT_Ticket ? $this->mapper->toDomain($row) : null;
    }

    public function enqueue(KitchenTicket $ticket): void
    {
        if ($ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет уже сохранён — используй save.');
        }

        $row = KIT_Ticket::query()->create($this->mapper->toPersistence($ticket));
        $ticket->assignId((int) $row->id);
    }

    public function save(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет ещё не в очереди — используй enqueue.');
        }

        KIT_Ticket::query()
            ->whereKey($ticket->id())
            ->update($this->mapper->toPersistence($ticket));
    }

    public function listActive(): array
    {
        return KIT_Ticket::query()
            ->whereIn('status', [
                KitchenTicketStatus::Queued->value,
                KitchenTicketStatus::Started->value,
            ])
            ->orderBy('id')
            ->get()
            ->map(fn (KIT_Ticket $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }
}
