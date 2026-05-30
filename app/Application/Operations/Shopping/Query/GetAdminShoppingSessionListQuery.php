<?php

namespace App\Application\Operations\Shopping\Query;

use App\Application\Operations\Shopping\Contracts\AdminShoppingSessionReadRepository;
use App\Application\Operations\Shopping\DTO\AdminActiveCartListFilters;
use App\Application\Operations\Shopping\Presenter\AdminShoppingSessionPresenter;

final class GetAdminShoppingSessionListQuery
{
    public function __construct(
        private readonly AdminShoppingSessionReadRepository $sessions,
        private readonly AdminShoppingSessionPresenter $presenter,
    ) {}

    public function execute(
        ?string $search = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $filters = AdminActiveCartListFilters::fromSearch($search);

        $result = $this->sessions->paginateActiveCarts(
            page: $page,
            perPage: $perPage,
            clientId: $filters->clientId,
            sessionId: $filters->sessionId,
            publicId: $filters->publicId,
            orderId: $filters->orderId,
        );

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
