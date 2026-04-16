<?php

namespace App\Application\YandexFood;

use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Product\Repository\ProductRepository;

abstract class YandexFoodBaseUseCase
{
    public function __construct(
        protected readonly ?ProductRepository $products = null,
        protected readonly ?CategoryRepository $categories = null,
    ) {
    }
}
