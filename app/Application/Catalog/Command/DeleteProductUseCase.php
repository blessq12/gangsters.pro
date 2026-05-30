<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Contracts\ProductDeletionGuardPort;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Repository\ProductRepository;

final class DeleteProductUseCase
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductDeletionGuardPort $guard,
    ) {
    }

    public function execute(int $productId): void
    {
        $product = $this->products->findById($productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        $this->guard->assertDeletable($productId);
        $this->products->delete($product);
    }
}
