<?php

namespace App\Domain\Crm\Port;

/**
 * Заказы клиента для CRM: только массивы, без сущностей Order.
 *
 * Снимок заказа: {id, source, status, total_rubles, created_at, delivery_method, payment_method, lines}
 * Строка: {product_id, product_name, quantity, unit_price_rubles, line_total_rubles, kind}
 */
interface CrmClientOrdersPort
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listByClientId(int $clientId): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForClient(int $orderId, int $clientId): ?array;
}
