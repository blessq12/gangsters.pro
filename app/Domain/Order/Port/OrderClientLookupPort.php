<?php

namespace App\Domain\Order\Port;

/**
 * Снимок клиента для quote/place: только массивы, без сущностей Crm.
 */
interface OrderClientLookupPort
{
    /**
     * @return array{id: int, name: string, phone: string, email: ?string}|null
     */
    public function findSnapshotById(int $clientId): ?array;
}
