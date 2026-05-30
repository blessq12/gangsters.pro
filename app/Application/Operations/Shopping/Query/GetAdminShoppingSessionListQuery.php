<?php

namespace App\Application\Operations\Shopping\Query;

use App\Application\Operations\Shopping\Contracts\AdminShoppingSessionReadRepository;
use App\Application\Operations\Shopping\Presenter\AdminShoppingSessionPresenter;

final class GetAdminShoppingSessionListQuery
{
    public function __construct(
        private readonly AdminShoppingSessionReadRepository $sessions,
        private readonly AdminShoppingSessionPresenter $presenter,
    ) {
    }

    public function execute(int $page = 1, int $perPage = 25): array
    {
        $result = $this->sessions->paginateActiveCarts($page, $perPage);

        return [
            'items' => array_map(
                fn (array $row): array => $this->presenter->presentListItem($row),
                $result['items'],
            ),
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
