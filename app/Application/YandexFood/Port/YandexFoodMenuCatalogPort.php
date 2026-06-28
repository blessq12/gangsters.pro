<?php

namespace App\Application\YandexFood\Port;

interface YandexFoodMenuCatalogPort
{
    /**
     * @return array{
     *     categories: list<array{category: \App\Domain\Catalog\Entity\Category, has_items: bool}>,
     *     products: list<array{partner_sku: string, category_id: int, product: \App\Domain\Catalog\Entity\Product, sort_order: int}>
     * }
     */
    public function readCompositionCatalog(): array;

    /**
     * @return list<string>
     */
    public function readUnavailablePartnerSkus(): array;
}
