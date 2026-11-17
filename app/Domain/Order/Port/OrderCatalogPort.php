<?php

namespace App\Domain\Order\Port;

/**
 * Чтение каталога для оформления заказа: только массивы, без сущностей Catalog.
 *
 * Снимок товара: {id, name, sku, price_rubles, is_active, is_system, ingredients, image_paths}
 * Снимок набора: {id, name, sku, price_rubles, lines: [{product_id, quantity}]}
 */
interface OrderCatalogPort
{
    /**
     * @param  list<int>  $ids
     * @return list<array<string, mixed>>
     */
    public function findActiveProductsByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return list<array<string, mixed>>
     */
    public function findActiveSetsByIds(array $ids): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findProductById(int $id): ?array;
}
