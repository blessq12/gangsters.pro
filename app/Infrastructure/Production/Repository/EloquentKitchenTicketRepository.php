<?php

namespace App\Infrastructure\Production\Repository;

use App\Domain\Production\Entity\KitchenTicket;
use App\Domain\Production\Enum\KitchenTicketStatus;
use App\Domain\Production\Repository\KitchenTicketRepository;
use App\Infrastructure\Production\Mapper\KitchenTicketMapper;
use App\Infrastructure\Production\Model\PRD_KitchenTicket;

final class EloquentKitchenTicketRepository implements KitchenTicketRepository
{
    public function __construct(
        private readonly KitchenTicketMapper $mapper,
    ) {}

    public function findByOrderId(int $orderId): ?KitchenTicket
    {
        $row = PRD_KitchenTicket::query()
            ->where('order_id', $orderId)
            ->first();

        return $row instanceof PRD_KitchenTicket ? $this->mapper->toDomain($row) : null;
    }

    public function enqueue(KitchenTicket $ticket): void
    {
        if ($ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет уже сохранён — используй save.');
        }

        $row = PRD_KitchenTicket::query()->create($this->mapper->toPersistence($ticket));
        $ticket->assignId((int) $row->id);
    }

    public function save(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет ещё не в очереди — используй enqueue.');
        }

        PRD_KitchenTicket::query()
            ->whereKey($ticket->id())
            ->update($this->mapper->toPersistence($ticket));
    }

    public function listActive(): array
    {
        return PRD_KitchenTicket::query()
            ->whereIn('status', [
                KitchenTicketStatus::Queued->value,
                KitchenTicketStatus::Started->value,
            ])
            ->orderBy('id')
            ->get()
            ->map(fn (PRD_KitchenTicket $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }
}
