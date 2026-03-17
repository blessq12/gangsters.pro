<?php

namespace App\Domain\Client\Events;

use App\Domain\Client\Entity\Client;
use App\Shared\Events\DomainEvent;

final class ClientPasswordChanged implements DomainEvent
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function client(): Client
    {
        return $this->client;
    }
}

