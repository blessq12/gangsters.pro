<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Catalog\Repository\CategoryRepository;

final class ListAdminCategoriesUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        $items = [];
        foreach ($this->categories->listPage($offset, $limit) as $category) {
            $items[] = $this->presenter->category($category);
        }

        return PageParams::wrap(
            $items,
            $this->categories->countAll(),
            $offset,
            $limit,
        );
    }
}
