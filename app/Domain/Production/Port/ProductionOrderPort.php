<?php

namespace App\Domain\Production\Port;

/**
 * Состав заказа для кухни: только чтение, массивы без сущностей Order.
 *
 * Данные читаются из контекста Order через адаптер (реализация — позже).
 *
 * Строка: {product_id, product_name, quantity, kind}
 */
interface ProductionOrderPort
{
    /**
     * Снимок строк заказа.
     *
     * @return list<array<string, mixed>>
     */
    public function linesSnapshot(int $orderId): array;
}
