<?php

namespace App\Domain\Product\Events;

use App\Domain\Product\Entity\Product;
use App\Shared\Events\DomainEvent;

final class ProductCreated implements DomainEvent
{
    public function __construct(
        private readonly Product $product,
    ) {
    }

    public function product(): Product
    {
        return $this->product;
    }
}

