<?php

namespace App\Infrastructure\Catalog\Guard;

use App\Application\Catalog\Contracts\ProductDeletionGuardPort;
use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use App\Infrastructure\Order\Model\ORD_OrderItem;

final class EloquentProductDeletionGuard implements ProductDeletionGuardPort
{
    public function assertDeletable(int $productId): void
    {
        $inActiveOrder = ORD_OrderItem::query()
            ->where('product_original_id', $productId)
            ->whereHas('order', fn ($query) => $query->where('status', '!=', 'delivered'))
            ->exists();

        if ($inActiveOrder) {
            throw new ApiException(
                'Товар используется в активных заказах. Вместо удаления используйте архивацию.',
                422,
            );
        }

        if (PRD_CategoryProduct::query()->where('product_id', $productId)->exists()) {
            throw new ApiException(
                'Товар присутствует в раскладке каталога. Сначала уберите его из категорий или используйте архивацию.',
                422,
            );
        }
    }
}
