<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Repository\ProductRepository;

final class GetAdminProductFormQuery
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $presenter,
    ) {
    }

    public function execute(int $productId): array
    {
        $readModel = $this->products->findByIdForAdminForm($productId);
        if ($readModel === null) {
            throw new ApiException('Product not found.', 404);
        }

        return $this->presenter->presentFormDetail($readModel);
    }
}
