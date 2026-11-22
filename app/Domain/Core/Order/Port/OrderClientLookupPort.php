<?php

namespace App\Domain\Core\Order\Port;

/**
 * Поиск клиента для снимка при оформлении. Order Domain не импортирует Customer/CRM.
 *
 * @phpstan-type OrderClientLookupSnapshot array{
 *     id: int,
 *     name: string,
 *     phone: string,
 *     email: string|null
 * }
 */
interface OrderClientLookupPort
{
    /**
     * @return OrderClientLookupSnapshot|null
     */
    public function findById(int $id): ?array;
}
