<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Order\Port\OrderClientLookupPort;

final class OrderClientLookupAdapter implements OrderClientLookupPort
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {}

    public function findSnapshotById(int $clientId): ?array
    {
        $client = $this->clients->findById($clientId);

        if (! $client instanceof Client) {
            return null;
        }

        return [
            'id' => $client->id(),
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
        ];
    }
}
