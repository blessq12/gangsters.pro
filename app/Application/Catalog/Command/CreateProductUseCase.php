<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\CreateProductDTO;
use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Application\Catalog\Support\CatalogProductMapper;
use App\Domain\Product\Repository\ProductRepository;

final class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $presenter,
        private readonly CatalogEventPublisher $events,
    ) {
    }

    public function execute(CreateProductDTO $dto): array
    {
        $product = CatalogProductMapper::productFromCreateDto($dto);
        $this->products->save($product);
        $this->events->productCreated($product);

        return $this->presenter->presentDetail($product);
    }
}
