<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var array<int, Order> */
    private array $orders = [];

    private int $nextId = 1;

    public function findById(int $id): ?Order
    {
        return $this->orders[$id] ?? null;
    }

    public function findByClientRequestId(string $clientRequestId): ?Order
    {
        foreach ($this->orders as $order) {
            if ($order->clientRequestId() === $clientRequestId) {
                return $order;
            }
        }

        return null;
    }

    public function listByClientId(int $clientId): array
    {
        return array_values(array_filter(
            $this->orders,
            static fn (Order $order): bool => $order->clientId() === $clientId,
        ));
    }

    public function count(?string $status = null): int
    {
        return count($this->listPage(0, PHP_INT_MAX, $status));
    }

    public function countByStatus(): array
    {
        $counts = [];
        foreach ($this->orders as $order) {
            $key = $order->status()->value;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    public function listPage(int $offset, int $limit, ?string $status = null): array
    {
        $rows = array_values($this->orders);
        if ($status !== null) {
            $rows = array_values(array_filter(
                $rows,
                static fn (Order $order): bool => $order->status()->value === $status,
            ));
        }

        return array_slice($rows, $offset, $limit);
    }

    public function save(Order $order): void
    {
        if (! $order->hasId()) {
            $order->assignId($this->nextId++);
        }

        $this->orders[$order->id()] = $order;
    }
}
