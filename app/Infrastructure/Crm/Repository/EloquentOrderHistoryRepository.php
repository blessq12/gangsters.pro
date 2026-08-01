<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Entity\OrderHistoryEntry;
use App\Domain\Crm\Repository\OrderHistoryRepository;
use App\Infrastructure\Crm\Mapper\OrderHistoryMapper;
use App\Infrastructure\Crm\Model\CRM_OrderHistory;

final class EloquentOrderHistoryRepository implements OrderHistoryRepository
{
    public function __construct(
        private readonly OrderHistoryMapper $mapper,
    ) {}

    public function save(OrderHistoryEntry $entry): void
    {
        $payload = $this->mapper->toPersistence($entry);

        if (! $entry->hasId()) {
            $row = CRM_OrderHistory::query()->create($payload);
            $entry->assignId((int) $row->id);

            return;
        }

        CRM_OrderHistory::query()
            ->whereKey($entry->id())
            ->update($payload);
    }

    public function findById(int $id): ?OrderHistoryEntry
    {
        $row = CRM_OrderHistory::query()->find($id);

        return $row instanceof CRM_OrderHistory ? $this->mapper->toDomain($row) : null;
    }

    public function listByClientId(int $clientId): array
    {
        $rows = CRM_OrderHistory::query()
            ->where('client_id', $clientId)
            ->orderByDesc('placed_at')
            ->get();

        $entries = [];
        foreach ($rows as $row) {
            if ($row instanceof CRM_OrderHistory) {
                $entries[] = $this->mapper->toDomain($row);
            }
        }

        return $entries;
    }
}
