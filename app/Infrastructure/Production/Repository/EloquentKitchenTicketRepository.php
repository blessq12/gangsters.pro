<?php

namespace App\Infrastructure\Production\Repository;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Infrastructure\Production\Mapper\KitchenTicketMapper;
use App\Infrastructure\Production\Model\KIT_Ticket;
use App\Infrastructure\Production\Model\KIT_TicketLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentKitchenTicketRepository implements KitchenTicketRepository
{
    public function __construct(
        private readonly KitchenTicketMapper $mapper,
    ) {}

    public function findByOrderId(int $orderId): ?KitchenTicket
    {
        $row = KIT_Ticket::query()
            ->with('lines')
            ->where('order_id', $orderId)
            ->first();

        return $row instanceof KIT_Ticket ? $this->mapper->toDomain($row) : null;
    }

    public function enqueue(KitchenTicket $ticket): void
    {
        if ($ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет уже сохранён — используй save.');
        }

        DB::transaction(function () use ($ticket): void {
            $payload = $this->mapper->toPersistence($ticket);
            $row = KIT_Ticket::query()->create($payload['ticket']);
            $ticket->assignId((int) $row->id);
            $this->replaceLines($ticket->id(), $payload['lines']);
        });
    }

    public function save(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет ещё не в очереди — используй enqueue.');
        }

        DB::transaction(function () use ($ticket): void {
            $payload = $this->mapper->toPersistence($ticket);
            KIT_Ticket::query()
                ->whereKey($ticket->id())
                ->update($payload['ticket']);
            $this->replaceLines($ticket->id(), $payload['lines']);
        });
    }

    public function delete(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Нельзя удалить тикет без id.');
        }

        DB::transaction(function () use ($ticket): void {
            KIT_TicketLine::query()->where('ticket_id', $ticket->id())->delete();
            KIT_Ticket::query()->whereKey($ticket->id())->delete();
        });
    }

    public function listActiveByKitchen(int $kitchenId): array
    {
        return $this->listActiveByKitchenPage($kitchenId, 0, max(1, $this->countActiveByKitchen($kitchenId)));
    }

    public function listActiveByKitchenPage(int $kitchenId, int $offset, int $limit): array
    {
        return $this->activeByKitchenQuery($kitchenId)
            ->with('lines')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (KIT_Ticket $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }

    public function countActiveByKitchen(int $kitchenId): int
    {
        return (int) $this->activeByKitchenQuery($kitchenId)->count();
    }

    /**
     * @return Builder<KIT_Ticket>
     */
    private function activeByKitchenQuery(int $kitchenId): Builder
    {
        return KIT_Ticket::query()
            ->where('kitchen_id', $kitchenId)
            ->whereIn('status', [
                KitchenTicketStatus::Queued->value,
                KitchenTicketStatus::Started->value,
            ]);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function replaceLines(int $ticketId, array $lines): void
    {
        KIT_TicketLine::query()->where('ticket_id', $ticketId)->delete();

        foreach ($lines as $line) {
            KIT_TicketLine::query()->create([
                ...$line,
                'ticket_id' => $ticketId,
            ]);
        }
    }
}
