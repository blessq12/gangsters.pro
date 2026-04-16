<?php

namespace App\Application\Catalog\Contracts;

interface CatalogYandexReadModelContract
{
    /**
     * @return list<array{
     *   category: array{id: string, name: string, sortOrder: int},
     *   lines: list<array{product: array{id: string, name: string, description: string, priceRubles: float}, sortOrder: int}>
     * }>
     */
    public function getActiveMenuBlocks(): array;

    /**
     * @return list<string>
     */
    public function getUnavailableProductIds(): array;
}
