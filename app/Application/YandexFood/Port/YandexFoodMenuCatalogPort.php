<?php

namespace App\Application\YandexFood\Port;

interface YandexFoodMenuCatalogPort
{
    /**
     * @return array{
     *     categories: list<array{id: int, name: string, sort_order: int, has_items: bool}>,
     *     products: list<array{
     *         id: int,
     *         category_id: int,
     *         name: string,
     *         description: string,
     *         price_rubles: int,
     *         sort_order: int,
     *         image_paths: list<string>
     *     }>
     * }
     */
    public function readCompositionCatalog(): array;

    /**
     * @return list<string>
     */
    public function readUnavailableProductIds(): array;
}
