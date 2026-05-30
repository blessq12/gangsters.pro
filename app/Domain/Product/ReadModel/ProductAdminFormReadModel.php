<?php

namespace App\Domain\Product\ReadModel;

use App\Domain\Product\Entity\Product;

final readonly class ProductAdminFormReadModel
{
    public function __construct(
        public Product $product,
        public string $slug,
        public int $imagesCount,
    ) {
    }
}
