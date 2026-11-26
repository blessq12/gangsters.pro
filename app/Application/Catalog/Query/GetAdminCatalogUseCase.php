<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\Repository\TechCardRepository;

/**
 * Полный справочник товаров для админки.
 */
final class GetAdminCatalogUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
        private readonly TechCardRepository $techCards,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array{products: list<array<string, mixed>>}
     */
    public function execute(): array
    {
        $all = $this->items->listAllProducts();
        $productIds = [];
        foreach ($all as $product) {
            $productIds[] = $product->id();
        }
        $techCards = $this->techCards->findByProductIds($productIds);
        $primaryImages = $this->items->findPrimaryImagesByItemIds($productIds);

        $products = [];
        foreach ($all as $product) {
            $row = $this->presenter->adminProduct($product);
            $row['ingredients'] = $product->ingredients();
            $row['tech_card_search'] = $this->presenter->techCardSearchText(
                $techCards[$product->id()] ?? null,
            );
            $image = $primaryImages[$product->id()] ?? null;
            $row['image_url'] = is_array($image) ? (string) ($image['url'] ?? '') : '';
            $row['image_focus_x'] = is_array($image) ? $image['focus_x'] : null;
            $row['image_focus_y'] = is_array($image) ? $image['focus_y'] : null;
            $products[] = $row;
        }

        return ['products' => $products];
    }
}
