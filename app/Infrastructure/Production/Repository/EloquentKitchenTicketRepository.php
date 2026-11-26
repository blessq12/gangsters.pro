<?php

namespace App\Infrastructure\Production\Repository;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Infrastructure\Production\Mapper\KitchenTicketMapper;
use App\Infrastructure\Production\Model\KitchenTicketLineModel;
use App\Infrastructure\Production\Model\KitchenTicketModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentKitchenTicketRepository implements KitchenTicketRepository
{
    public function __construct(
        private readonly KitchenTicketMapper $mapper,
    ) {}

    public function findByOrderId(int $orderId): ?KitchenTicket
    {
        $row = KitchenTicketModel::query()
            ->with('lines')
            ->where('order_id', $orderId)
            ->first();

        return $row instanceof KitchenTicketModel ? $this->mapper->toDomain($row) : null;
    }

    public function enqueue(KitchenTicket $ticket): void
    {
        if ($ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет уже сохранён — используй save.');
        }

        DB::transaction(function () use ($ticket): void {
            $payload = $this->mapper->toPersistence($ticket);
            $row = KitchenTicketModel::query()->create($payload['ticket']);
            $ticket->assignId((int) $row->id);
            $this->syncLines($ticket);
        });
    }

    public function save(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет ещё не в очереди — используй enqueue.');
        }

        DB::transaction(function () use ($ticket): void {
            $payload = $this->mapper->toPersistence($ticket);
            KitchenTicketModel::query()
                ->whereKey($ticket->id())
                ->update($payload['ticket']);
            $this->syncLines($ticket);
        });
    }

    public function delete(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Нельзя удалить тикет без id.');
        }

        DB::transaction(function () use ($ticket): void {
            KitchenTicketLineModel::query()->where('ticket_id', $ticket->id())->delete();
            KitchenTicketModel::query()->whereKey($ticket->id())->delete();
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
            ->map(fn (KitchenTicketModel $row): KitchenTicket => $this->mapper->toDomain($row))
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
            ->map(fn (KitchenTicketModel $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }

    public function countQueuedByBranch(int $branchId): int
    {
        return (int) $this->queuedByBranchQuery($branchId)->count();
    }

    public function listMineStartedByBranch(int $branchId, int $employeeId): array
    {
        return KitchenTicketModel::query()
            ->with('lines')
            ->where('branch_id', $branchId)
            ->where('status', KitchenTicketStatus::Started->value)
            ->where('assignee_employee_id', $employeeId)
            ->orderBy('id')
            ->get()
            ->map(fn (KitchenTicketModel $row): KitchenTicket => $this->mapper->toDomain($row))
            ->all();
    }

    public function countActiveByStatus(?int $branchId = null): array
    {
        $query = KitchenTicketModel::query()
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
     * @return Builder<KitchenTicketModel>
     */
    private function activeByBranchQuery(int $branchId): Builder
    {
        return KitchenTicketModel::query()
            ->where('branch_id', $branchId)
            ->whereIn('status', [
                KitchenTicketStatus::Queued->value,
                KitchenTicketStatus::Started->value,
            ]);
    }

    /**
     * @return Builder<KitchenTicketModel>
     */
    private function queuedByBranchQuery(int $branchId): Builder
    {
        return KitchenTicketModel::query()
            ->where('branch_id', $branchId)
            ->where('status', KitchenTicketStatus::Queued->value);
    }

    private function syncLines(KitchenTicket $ticket): void
    {
        $ticketId = $ticket->id();
        $keepIds = [];

        foreach (array_values($ticket->lines()) as $index => $line) {
            $payload = [
                'ticket_id' => $ticketId,
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'position' => $index,
                'status' => $line->status()->value,
                'done_by' => $line->doneBy(),
                'done_at' => $line->doneAt()?->format('Y-m-d H:i:s'),
                'packed_by' => $line->packedBy(),
                'packed_at' => $line->packedAt()?->format('Y-m-d H:i:s'),
            ];

            if ($line->hasId()) {
                $id = $line->id();
                KitchenTicketLineModel::query()
                    ->whereKey($id)
                    ->where('ticket_id', $ticketId)
                    ->update($payload);
                $keepIds[] = $id;

                continue;
            }

            $row = KitchenTicketLineModel::query()->create($payload);
            $line->assignId((int) $row->id);
            $keepIds[] = (int) $row->id;
        }

        $deleteQuery = KitchenTicketLineModel::query()->where('ticket_id', $ticketId);
        if ($keepIds !== []) {
            $deleteQuery->whereNotIn('id', $keepIds);
        }
        $deleteQuery->delete();
    }
}
