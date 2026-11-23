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

    public function listActiveByBranch(int $branchId): array
    {
        return $this->listActiveByBranchPage($branchId, 0, max(1, $this->countActiveByBranch($branchId)));
    }

    public function listActiveByBranchPage(int $branchId, int $offset, int $limit): array
    {
        return $this->activeByBranchQuery($branchId)
            ->with('lines')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (KIT_Ticket $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }

    public function countActiveByBranch(int $branchId): int
    {
        return (int) $this->activeByBranchQuery($branchId)->count();
    }

    public function listQueuedByBranchPage(int $branchId, int $offset, int $limit): array
    {
        return $this->queuedByBranchQuery($branchId)
            ->with('lines')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (KIT_Ticket $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }

    public function countQueuedByBranch(int $branchId): int
    {
        return (int) $this->queuedByBranchQuery($branchId)->count();
    }

    public function listMineStartedByBranch(int $branchId, int $employeeId): array
    {
        return KIT_Ticket::query()
            ->with('lines')
            ->where('branch_id', $branchId)
            ->where('status', KitchenTicketStatus::Started->value)
            ->where('assignee_employee_id', $employeeId)
            ->orderBy('id')
            ->get()
            ->map(fn (KIT_Ticket $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }

    public function countActiveByStatus(?int $branchId = null): array
    {
        $query = KIT_Ticket::query()
            ->whereIn('status', [
                KitchenTicketStatus::Queued->value,
                KitchenTicketStatus::Started->value,
            ]);
        if ($branchId !== null && $branchId > 0) {
            $query->where('branch_id', $branchId);
        }

        $queued = 0;
        $started = 0;
        $rows = $query
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->get();
        foreach ($rows as $row) {
            $status = (string) $row->status;
            $count = (int) $row->aggregate;
            if ($status === KitchenTicketStatus::Queued->value) {
                $queued = $count;
            }
            if ($status === KitchenTicketStatus::Started->value) {
                $started = $count;
            }
        }

        return [
            'queued' => $queued,
            'started' => $started,
        ];
    }

    /**
     * @return Builder<KIT_Ticket>
     */
    private function activeByBranchQuery(int $branchId): Builder
    {
        return KIT_Ticket::query()
            ->where('branch_id', $branchId)
            ->whereIn('status', [
                KitchenTicketStatus::Queued->value,
                KitchenTicketStatus::Started->value,
            ]);
    }

    /**
     * @return Builder<KIT_Ticket>
     */
    private function queuedByBranchQuery(int $branchId): Builder
    {
        return KIT_Ticket::query()
            ->where('branch_id', $branchId)
            ->where('status', KitchenTicketStatus::Queued->value);
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
