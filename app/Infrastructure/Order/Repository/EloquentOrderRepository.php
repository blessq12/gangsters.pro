<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\ValueObject\OrderId;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\ORD_Order;

final class EloquentOrderRepository implements OrderRepository
{
    public function __construct(
        private readonly OrderMapper $mapper,
    ) {}

    public function findById(OrderId $id): ?Order
    {
        $row = ORD_Order::query()->find($id->value());

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
    }

    public function findByCheckoutId(string $checkoutId): ?Order
    {
        $row = ORD_Order::query()
            ->where('checkout_id', $checkoutId)
            ->first();

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
    }

    public function findByPartnerAndExternalOrderId(string $partnerCode, string $externalOrderId): ?Order
    {
        $row = ORD_Order::query()
            ->where('partner_code', $partnerCode)
            ->where('external_order_id', $externalOrderId)
            ->first();

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
    }

    public function existsByCheckoutId(string $checkoutId): bool
    {
        return ORD_Order::query()
            ->where('checkout_id', $checkoutId)
            ->exists();
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
            $order->assignId(OrderId::fromInt((int) $row->id));

            return;
        }

        ORD_Order::query()->updateOrCreate(
            ['id' => $payload['id']],
            $payload,
        );
    }
}
