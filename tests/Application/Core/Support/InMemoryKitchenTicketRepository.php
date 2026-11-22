<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Production\Entity\KitchenTicket;
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
        $this->byOrderId[$ticket->orderId()] = $ticket;
    }

    public function save(KitchenTicket $ticket): void
    {
        if (! $ticket->hasId()) {
            throw new \InvalidArgumentException('Тикет ещё не в очереди — используй enqueue.');
        }

        $this->byOrderId[$ticket->orderId()] = $ticket;
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

    public function listActiveByBranch(int $branchId): array
    {
        return array_values(array_filter(
            $this->byOrderId,
            static fn (KitchenTicket $ticket): bool => $ticket->branchId() === $branchId,
        ));
    }
}
