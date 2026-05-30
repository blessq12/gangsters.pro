<?php

namespace App\Application\Catalog\DTO;

final readonly class SetCategoryProductsDTO
{
    /**
     * @param  int[]  $productIds  Ordered product ids for the category
     */
    public function __construct(
        public int $categoryId,
        public array $productIds,
    ) {
    }
}
