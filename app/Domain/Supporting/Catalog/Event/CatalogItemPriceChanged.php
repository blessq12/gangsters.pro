<?php

namespace App\Domain\Supporting\Catalog\Event;

/**
 * Регулярная цена позиции каталога (товар или набор) изменилась.
 */
final readonly class CatalogItemPriceChanged
{
    public function __construct(
        public int $catalogItemId,
        public string $name,
        public int $regularPriceKopecks,
    ) {}
}
