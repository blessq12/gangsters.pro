<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\UpdateProductDTO;
use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Application\Catalog\Support\CatalogProductMapper;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Repository\ProductRepository;

final class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $presenter,
        private readonly CatalogEventPublisher $events,
    ) {
    }

    public function execute(UpdateProductDTO $dto): array
    {
        $product = $this->products->findById($dto->productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        CatalogProductMapper::applyUpdateDto($product, $dto);
        $this->products->save($product);
        $this->events->productUpdated($product);

        return $this->presenter->presentDetail($product);
    }
}
