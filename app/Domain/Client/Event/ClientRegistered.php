<?php

namespace App\Domain\Client\Event;

use App\Domain\Client\ValueObject\ClientId;

final readonly class ClientRegistered
{
    public function __construct(
        public ClientId $clientId,
    ) {}
}
