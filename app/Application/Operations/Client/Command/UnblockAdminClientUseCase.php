<?php

namespace App\Application\Operations\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Events\ClientProfileUpdated;
use App\Domain\Client\Repository\ClientRepository;
use App\Shared\Events\DomainEventBus;

final class UnblockAdminClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly AdminClientPresenter $presenter,
        private readonly DomainEventBus $events,
    ) {
    }

    public function execute(int $clientId): array
    {
        $client = $this->requireClient($clientId);

        if ($client->status() === Client::STATUS_ACTIVE) {
            throw new ApiException('Client is already active.', 422);
        }

        $client->activate();
        $this->clients->save($client);
        $this->events->publish(new ClientProfileUpdated($client));

        return [
            'client' => $this->presenter->presentDetail($client),
        ];
    }

    private function requireClient(int $clientId): Client
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new ApiException('Client not found.', 404);
        }

        return $client;
    }
}
