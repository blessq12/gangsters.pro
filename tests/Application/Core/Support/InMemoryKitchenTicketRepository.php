<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;

final class InMemoryKitchenTicketRepository implements KitchenTicketRepository
{
    /** @var array<int, KitchenTicket> */
    private array $byOrderId = [];

    private int $nextId = 1;

    public function findByOrderId(int $orderId): ?KitchenTicket
    {
        return $this->byOrderId[$orderId] ?? null;
    }

    public function enqueue(KitchenTicket $ticket): void
    {
        if ($ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет уже сохранён — используй save.');
        }

        $ticket->assignId($this->nextId++);
        $this->assignLineIds($ticket);
        $this->byOrderId[$ticket->orderId()] = $ticket;
    }

    public function save(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет ещё не в очереди — используй enqueue.');
        }

        $this->assignLineIds($ticket);
        $this->byOrderId[$ticket->orderId()] = $ticket;
    }

    private function assignLineIds(KitchenTicket $ticket): void
    {
        foreach ($ticket->lines() as $line) {
            if (! $line->hasId()) {
                $line->assignId($this->nextId++);
            }
        }
    }

    public function delete(KitchenTicket $ticket): void
    {
        unset($this->byOrderId[$ticket->orderId()]);
    }

    public function listActiveByBranchPage(int $branchId, int $offset, int $limit): array
    {
        return array_slice($this->listActiveByBranch($branchId), $offset, $limit);
    }

    public function countActiveByBranch(int $branchId): int
    {
        return count($this->listActiveByBranch($branchId));
    }

    public function listQueuedByBranchPage(int $branchId, int $offset, int $limit): array
    {
        $queued = array_values(array_filter(
            $this->listActiveByBranch($branchId),
            static fn (KitchenTicket $ticket): bool => $ticket->status() === KitchenTicketStatus::Queued,
        ));

        return array_slice($queued, $offset, $limit);
    }

    public function countQueuedByBranch(int $branchId): int
    {
        return count(array_filter(
            $this->listActiveByBranch($branchId),
            static fn (KitchenTicket $ticket): bool => $ticket->status() === KitchenTicketStatus::Queued,
        ));
    }

    public function listMineStartedByBranch(int $branchId, int $employeeId): array
    {
        return array_values(array_filter(
            $this->listActiveByBranch($branchId),
            static fn (KitchenTicket $ticket): bool => $ticket->status() === KitchenTicketStatus::Started
                && $ticket->assigneeEmployeeId() === $employeeId,
        ));
    }

    public function countActiveByStatus(?int $branchId = null): array
    {
        $tickets = $branchId !== null && $branchId > 0
            ? $this->listActiveByBranch($branchId)
            : array_values($this->byOrderId);

        $queued = 0;
        $started = 0;
        foreach ($tickets as $ticket) {
            $status = $ticket->status()->value;
            if ($status === 'queued') {
                $queued++;
            }
            if ($status === 'started') {
                $started++;
            }
        }

        return [
            'queued' => $queued,
            'started' => $started,
        ];
    }

    public function listActiveByBranch(int $branchId): array
    {
        return array_values(array_filter(
            $this->byOrderId,
            static fn (KitchenTicket $ticket): bool => $ticket->branchId() === $branchId,
        ));
    }
}
