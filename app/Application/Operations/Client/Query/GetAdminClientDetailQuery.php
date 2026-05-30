<?php

namespace App\Application\Operations\Client\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Contracts\AdminClientReadRepository;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Application\Operations\Order\Query\GetAdminOrderListQuery;

final class GetAdminClientDetailQuery
{
    public function __construct(
        private readonly AdminClientReadRepository $clients,
        private readonly AdminClientPresenter $presenter,
        private readonly GetAdminOrderListQuery $orders,
    ) {
    }

    public function execute(int $clientId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new ApiException('Client not found.', 404);
        }

        $orders = $this->orders->execute(clientId: $clientId, page: 1, perPage: 20);

        return [
            'client' => $this->presenter->presentDetail($client),
            'orders' => $orders,
        ];
    }
}
