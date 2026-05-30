<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Contracts\AdminProductReadRepository;
use App\Application\Catalog\Presenter\AdminProductPresenter;

final class GetAdminProductListQuery
{
    public function __construct(
        private readonly AdminProductReadRepository $products,
        private readonly AdminProductPresenter $presenter,
    ) {
    }

    public function execute(
        ?string $search = null,
        ?string $status = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $result = $this->products->paginate($search, $status, $page, $perPage);

        return [
            'items' => array_map(
                fn ($product) => $this->presenter->presentListItem($product),
                $result['items'],
            ),
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
