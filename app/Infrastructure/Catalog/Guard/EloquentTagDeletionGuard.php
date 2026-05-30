<?php

namespace App\Infrastructure\Catalog\Guard;

use App\Application\Catalog\Contracts\TagDeletionGuardPort;
use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Product\Model\PRD_Tag;

final class EloquentTagDeletionGuard implements TagDeletionGuardPort
{
    public function assertDeletable(int $tagId): void
    {
        $tag = PRD_Tag::query()->find($tagId);
        if ($tag === null) {
            throw new ApiException('Tag not found.', 404);
        }

        if ($tag->products()->exists()) {
            throw new ApiException(
                'Тег привязан к товарам. Сначала отвяжите тег от всех товаров.',
                422,
            );
        }
    }
}
