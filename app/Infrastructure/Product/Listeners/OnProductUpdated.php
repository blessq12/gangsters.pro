<?php

namespace App\Infrastructure\Product\Listeners;

use App\Domain\Product\Events\ProductUpdated;

final class OnProductUpdated
{
    public function handle(ProductUpdated $event): void
    {
        // TODO: действия при обновлении продукта
    }
}

