<?php

namespace App\Application\Order;

use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Repository\ProductRepository;

abstract class OrderBaseUseCase
{
    public function __construct(
        protected readonly OrderRepositoryInterface $orders,
        protected readonly OrderFactory $orderFactory,
        protected readonly ClientRepository $clients,
        protected readonly ProductRepository $products,
    ) {
    }
}
