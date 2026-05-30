<?php

namespace App\Infrastructure\Catalog\Guard;

use App\Application\Catalog\Contracts\CategoryDeletionGuardPort;
use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Category\Model\PRD_CategoryProduct;

final class EloquentCategoryDeletionGuard implements CategoryDeletionGuardPort
{
    public function assertDeletable(int $categoryId): void
    {
        if (PRD_CategoryProduct::query()->where('category_id', $categoryId)->exists()) {
            throw new ApiException(
                'В категории есть товары в раскладке. Сначала удалите товары из категории.',
                422,
            );
        }
    }
}
