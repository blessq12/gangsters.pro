<?php

namespace App\Domain\Supporting\Client\Port;

/**
 * История заказов клиента для CRM. Crm Domain не импортирует Order.
 *
 * @phpstan-type CrmOrderLineSnapshot array{name: string, quantity: int, line_type: string}
 * @phpstan-type CrmOrderSnapshot array{
 *     id: int,
 *     number: string|null,
 *     status: string,
 *     created_at: string,
 *     grand_total_kopecks: int,
 *     lines: list<CrmOrderLineSnapshot>
 * }
 */
interface ClientOrdersPort
{
    /**
     * @return list<CrmOrderSnapshot>
     */
    public function listByClientId(int $clientId): array;

    /**
     * Сводки по заказам для листинга клиентов (batch, без N+1).
     *
     * @param  list<int>  $clientIds
     * @return array<int, array{orders_count: int, last_order_at: string|null}>
     */
    public function summariesByClientIds(array $clientIds): array;
}
