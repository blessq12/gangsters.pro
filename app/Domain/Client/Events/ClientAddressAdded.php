<?php

namespace App\Domain\Client\Events;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;
use App\Shared\Events\DomainEvent;

final class ClientAddressAdded implements DomainEvent
{
    public function __construct(
        private readonly Client $client,
        private readonly ClientAddress $address,
    ) {
    }

    public function client(): Client
    {
        return $this->client;
    }

    public function address(): ClientAddress
    {
        return $this->address;
    }
}

