<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\AdminCategoryPresenter;
use App\Domain\Category\Repository\CategoryRepository;

final class GetAdminCategoryListQuery
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly AdminCategoryPresenter $presenter,
    ) {
    }

    public function execute(): array
    {
        return array_map(
            fn ($category) => $this->presenter->presentListItem($category),
            $this->categories->findAllOrdered(),
        );
    }
}
