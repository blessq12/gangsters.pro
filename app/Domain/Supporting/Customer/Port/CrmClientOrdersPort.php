<?php

namespace App\Domain\Supporting\Customer\Port;

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
interface CrmClientOrdersPort
{
    /**
     * @return list<CrmOrderSnapshot>
     */
    public function listByClientId(int $clientId): array;
}
