<?php

namespace App\Infrastructure\Product\Listeners;

use App\Domain\Product\Events\ProductCreated;

final class OnProductCreated
{
    public function handle(ProductCreated $event): void
    {
        // TODO: действия при создании продукта (кеш, индексация, аналитика и т.п.)
    }
}

