<?php

namespace App\Infrastructure\Delivery\Adapter;

use App\Domain\Core\Delivery\Routing\Port\DeliveryOrderStopSnapshotPort;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderNoteRepository;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderNumber;

/**
 * ACL: Delivery читает снимок заказа через OrderRepository в Infrastructure.
 */
final class EloquentDeliveryOrderStopSnapshotPort implements DeliveryOrderStopSnapshotPort
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderNoteRepository $notes,
    ) {}

    public function findMany(array $orderIds): array
    {
        $ids = [];
        foreach ($orderIds as $orderId) {
            $id = (int) $orderId;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        $noteCounts = $this->notes->countsByOrderIds($ids);

        $map = [];
        foreach ($ids as $id) {
            $order = $this->orders->findById($id);
            if ($order instanceof Order) {
                $map[$id] = $this->snapshot($order, (int) ($noteCounts[$id] ?? 0));
            }
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Order $order, int $notesCount = 0): array
    {
        $delivery = $order->delivery();
        $client = $order->client();
        $address = $delivery->address();
        $id = $order->hasId() ? $order->id() : 0;

        return [
            'id' => $id,
            'number' => $id > 0 ? OrderNumber::format($id, $order->createdAt()) : null,
            'status' => $order->status()->value,
            'method' => $delivery->type()->value,
            'payment_method' => $order->paymentMethod(),
            'address' => $address === null ? null : [
                'street' => $address->street(),
                'house' => $address->house(),
                'entrance' => $address->entrance(),
                'floor' => $address->floor(),
                'apartment' => $address->apartment(),
                'comment' => $address->comment(),
            ],
            'client' => [
                'name' => $client->name(),
                'phone' => $client->phone(),
            ],
            'notes_count' => max(0, $notesCount),
        ];
    }
}
