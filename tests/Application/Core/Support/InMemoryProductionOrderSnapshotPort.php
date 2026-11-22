<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Order\ValueObject\OrderNumber;
use App\Domain\Core\Production\Port\ProductionOrderSnapshotPort;

final class InMemoryProductionOrderSnapshotPort implements ProductionOrderSnapshotPort
{
    public function __construct(
        private readonly InMemoryOrderRepository $orders,
    ) {}

    public function find(int $orderId): ?array
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            return null;
        }

        $lines = [];
        foreach ($order->lines() as $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'line_type' => $line->lineType()->value,
            ];
        }

        $id = $order->hasId() ? $order->id() : 0;

        return [
            'id' => $id,
            'number' => $id > 0 ? OrderNumber::format($id, $order->createdAt()) : null,
            'branch_id' => $order->branchId(),
            'status' => $order->status()->value,
            'comment' => $order->delivery()->address()?->comment(),
            'lines' => $lines,
        ];
    }

    public function findMany(array $orderIds): array
    {
        $map = [];
        foreach ($orderIds as $orderId) {
            $snapshot = $this->find((int) $orderId);
            if ($snapshot !== null) {
                $map[(int) $orderId] = $snapshot;
            }
        }

        return $map;
    }
}
