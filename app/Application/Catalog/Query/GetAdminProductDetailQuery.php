<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Repository\ProductRepository;

final class GetAdminProductDetailQuery
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $presenter,
    ) {
    }

    public function execute(int $productId): array
    {
        $product = $this->products->findById($productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        $slug = $this->products->findSlugByProductId($productId);

        return $this->presenter->presentDetail($product, $slug);
    }
}
