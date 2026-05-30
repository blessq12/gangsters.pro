<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Query\GetAdminCategoryDetailQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Category\Repository\CategoryRepository;

final class ActivateCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly GetAdminCategoryDetailQuery $categoryDetail,
    ) {
    }

    public function execute(int $categoryId): array
    {
        $category = $this->categories->findById($categoryId);
        if ($category === null) {
            throw new ApiException('Category not found.', 404);
        }

        $category->activate();
        $this->categories->save($category);

        return $this->categoryDetail->execute($categoryId);
    }
}
