<?php

namespace App\Infrastructure\Crm\Mapper;

use App\Domain\Crm\Entity\OrderHistoryEntry;
use App\Infrastructure\Crm\Model\CRM_OrderHistory;
use DateTimeImmutable;

final class OrderHistoryMapper
{
    public function toDomain(CRM_OrderHistory $row): OrderHistoryEntry
    {
        return OrderHistoryEntry::restore(
            id: (int) $row->id,
            clientId: (int) $row->client_id,
            orderSnapshot: is_array($row->order_snapshot) ? $row->order_snapshot : [],
            placedAt: new DateTimeImmutable((string) $row->placed_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(OrderHistoryEntry $entry): array
    {
        return [
            'client_id' => $entry->clientId(),
            'order_snapshot' => $entry->orderSnapshot(),
            'placed_at' => $entry->placedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
