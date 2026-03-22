<?php

namespace App\Application\YandexFood;

use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Repository\ProductRepository;

abstract class YandexFoodBaseUseCase
{
    public function __construct(
        protected readonly OrderRepositoryInterface $orders,
        protected readonly ProductRepository $products,
    ) {
    }
}
