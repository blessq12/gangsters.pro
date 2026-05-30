<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Repository\ProductRepository;

final class DeleteProductImageUseCase
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $presenter,
        private readonly CatalogEventPublisher $events,
    ) {
    }

    public function execute(int $productId, int $imageIndex): array
    {
        $product = $this->products->findById($productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        $images = $product->images();
        if (! isset($images[$imageIndex])) {
            throw new ApiException('Image not found.', 404);
        }

        unset($images[$imageIndex]);
        $product->setImages(array_values($images));
        $this->products->save($product);
        $this->events->productUpdated($product);

        return $this->presenter->presentDetail($product);
    }
}
