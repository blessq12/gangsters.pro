<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

/**
 * Полный справочник товаров для админки.
 */
final class GetAdminCatalogUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array{products: list<array<string, mixed>>}
     */
    public function execute(): array
    {
        $products = [];
        foreach ($this->items->listAllProducts() as $product) {
            $products[] = $this->presenter->adminProduct($product);
        }

        return ['products' => $products];
    }
}
