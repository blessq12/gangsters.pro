<?php

namespace App\Infrastructure\Production\Adapter;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderNumber;
use App\Domain\Core\Production\Port\ProductionOrderSnapshotPort;

/**
 * ACL: Production читает снимок заказа через OrderRepository в Infrastructure.
 */
final class EloquentProductionOrderSnapshotPort implements ProductionOrderSnapshotPort
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function find(int $orderId): ?array
    {
        $order = $this->orders->findById($orderId);

        return $order instanceof Order ? $this->snapshot($order) : null;
    }

    public function findMany(array $orderIds): array
    {
        $map = [];
        foreach ($orderIds as $orderId) {
            $id = (int) $orderId;
            if ($id < 1) {
                continue;
            }
            $snapshot = $this->find($id);
            if ($snapshot !== null) {
                $map[$id] = $snapshot;
            }
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Order $order): array
    {
        $delivery = $order->delivery();
        $comment = $delivery->address()?->comment();

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
            'comment' => $comment,
            'lines' => $lines,
        ];
    }
}
