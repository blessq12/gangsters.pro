<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderNumber;

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

    public function count(array $filters = []): int
    {
        return count($this->listPage(0, PHP_INT_MAX, $filters));
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

    public function listPage(int $offset, int $limit, array $filters = []): array
    {
        $rows = array_values($this->orders);

        $status = isset($filters['status']) ? trim((string) $filters['status']) : '';
        if ($status !== '') {
            $rows = array_values(array_filter(
                $rows,
                static fn (Order $order): bool => $order->status()->value === $status,
            ));
        }

        $branchId = isset($filters['branch_id']) ? (int) $filters['branch_id'] : 0;
        if ($branchId > 0) {
            $rows = array_values(array_filter(
                $rows,
                static fn (Order $order): bool => $order->branchId() === $branchId,
            ));
        }

        $method = isset($filters['delivery_method']) ? trim((string) $filters['delivery_method']) : '';
        if ($method === 'pickup' || $method === 'courier') {
            $rows = array_values(array_filter(
                $rows,
                static fn (Order $order): bool => $method === 'pickup'
                    ? $order->delivery()->isPickup()
                    : ! $order->delivery()->isPickup(),
            ));
        }

        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $parsedNumber = OrderNumber::tryParse($q);
            $rows = array_values(array_filter(
                $rows,
                static function (Order $order) use ($q, $parsedNumber): bool {
                    if ($parsedNumber !== null) {
                        return $order->hasId() && $order->id() === $parsedNumber['id'];
                    }
                    if (ctype_digit($q) && $order->hasId() && $order->id() === (int) $q) {
                        return true;
                    }
                    $name = mb_strtolower($order->client()->name());
                    $phone = mb_strtolower($order->client()->phone());
                    $needle = mb_strtolower($q);

                    return str_contains($name, $needle) || str_contains($phone, $needle);
                },
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
