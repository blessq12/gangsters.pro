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

    public function findByClientRequestId(string $clientRequestId): ?Order
    {
        $row = ORD_Order::query()
            ->where('checkout_id', $clientRequestId)
            ->first();

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
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
