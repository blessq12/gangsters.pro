<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\AdminCategoryPresenter;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Category\Repository\CategoryRepository;

final class GetAdminCategoryDetailQuery
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly AdminCategoryPresenter $presenter,
    ) {
    }

    public function execute(int $categoryId): array
    {
        $category = $this->categories->findById($categoryId);
        if ($category === null) {
            throw new ApiException('Category not found.', 404);
        }

        $links = $this->categories->findLinksByCategoryId($categoryId);

        return $this->presenter->presentDetail($category, $links);
    }
}
