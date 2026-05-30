<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Repository\ProductRepository;

final class ArchiveProductUseCase
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $presenter,
        private readonly CatalogEventPublisher $events,
    ) {
    }

    public function execute(int $productId): array
    {
        $product = $this->products->findById($productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        $product->archive();
        $this->products->save($product);
        $this->events->productArchived($product);

        return $this->presenter->presentDetail($product);
    }
}
