<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\ORD_Order;

final class EloquentOrderRepository implements OrderRepository
{
    public function __construct(
        private readonly OrderMapper $mapper,
    ) {}

    public function findById(int $id): ?Order
    {
        $row = ORD_Order::query()->find($id);

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
    }

    public function findByClientRequestId(string $clientRequestId): ?Order
    {
        $row = ORD_Order::query()
            ->where('checkout_id', $clientRequestId)
            ->first();

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
    }

    public function listByClientId(int $clientId): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ORD_Order> $rows */
        $rows = ORD_Order::query()
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return $rows
            ->map(fn (ORD_Order $row): Order => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(Order $order): void
    {
        $payload = $this->mapper->toPersistence($order);

        if (! $order->hasId()) {
            unset($payload['id']);
            $row = ORD_Order::query()->create($payload);
            $order->assignId((int) $row->id);

            return;
        }

        ORD_Order::query()->updateOrCreate(
            ['id' => $payload['id']],
            $payload,
        );
    }
}
