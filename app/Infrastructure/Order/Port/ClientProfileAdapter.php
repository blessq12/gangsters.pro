<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Order\Port\ClientProfilePort;
use App\Domain\Order\Port\RegisteredClientProfileQuote;

final class ClientProfileAdapter implements ClientProfilePort
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {}

    public function findRegisteredProfile(int $clientId): ?RegisteredClientProfileQuote
    {
        $client = $this->clients->findById(ClientId::fromInt($clientId));

        if ($client === null) {
            return null;
        }

        return new RegisteredClientProfileQuote(
            name: $client->name(),
            phone: $client->phone()->digits(),
            email: $client->email(),
        );
    }
}
