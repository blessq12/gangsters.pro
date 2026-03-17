<?php

namespace App\Infrastructure\Product\Listeners;

use App\Domain\Product\Events\ProductArchived;

final class OnProductArchived
{
    public function handle(ProductArchived $event): void
    {
        // TODO: действия при архивировании продукта
    }
}

