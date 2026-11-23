<?php

namespace App\Domain\Supporting\Client\Port;

/**
 * История заказов клиента для CRM. Crm Domain не импортирует Order.
 *
 * @phpstan-type CrmOrderSnapshot array{
 *     id: int,
 *     number: string|null,
 *     status: string,
 *     created_at: string,
 *     grand_total_kopecks: int
 * }
 */
interface ClientOrdersPort
{
    /**
     * @return list<CrmOrderSnapshot>
     */
    public function listByClientId(int $clientId): array;
}
