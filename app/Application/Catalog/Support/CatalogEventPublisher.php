<?php

namespace App\Application\Catalog\Support;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Events\ProductArchived;
use App\Domain\Product\Events\ProductCreated;
use App\Domain\Product\Events\ProductUpdated;
use App\Shared\Events\DomainEventBus;

final class CatalogEventPublisher
{
    public function __construct(
        private readonly DomainEventBus $events,
    ) {
    }

    public function productCreated(Product $product): void
    {
        $this->events->publish(new ProductCreated($product));
    }

    public function productUpdated(Product $product): void
    {
        $this->events->publish(new ProductUpdated($product));
    }

    public function productArchived(Product $product): void
    {
        $this->events->publish(new ProductArchived($product));
    }
}
