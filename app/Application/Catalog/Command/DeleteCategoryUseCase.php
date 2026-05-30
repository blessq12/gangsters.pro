<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Contracts\CategoryDeletionGuardPort;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Category\Repository\CategoryRepository;

final class DeleteCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CategoryDeletionGuardPort $guard,
    ) {
    }

    public function execute(int $categoryId): void
    {
        $category = $this->categories->findById($categoryId);
        if ($category === null) {
            throw new ApiException('Category not found.', 404);
        }

        $this->guard->assertDeletable($categoryId);
        $this->categories->delete($category);
    }
}
