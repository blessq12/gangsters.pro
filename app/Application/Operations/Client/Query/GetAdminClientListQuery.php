<?php

namespace App\Application\Operations\Client\Query;

use App\Application\Operations\Client\Contracts\AdminClientReadRepository;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Entity\Client;

final class GetAdminClientListQuery
{
    public function __construct(
        private readonly AdminClientReadRepository $clients,
        private readonly AdminClientPresenter $presenter,
    ) {
    }

    public function execute(
        ?string $search = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $result = $this->clients->paginate($search, $page, $perPage);

        return [
            'items' => array_map(
                fn (Client $client) => $this->presenter->presentListItem($client),
                $result['items'],
            ),
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
