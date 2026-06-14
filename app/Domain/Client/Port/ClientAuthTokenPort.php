<?php

namespace App\Domain\Client\Port;

use App\Domain\Client\ValueObject\ClientId;

interface ClientAuthTokenPort
{
    public function issueToken(ClientId $clientId): string;
}
