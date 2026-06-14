<?php

namespace App\Infrastructure\Client\Auth;

use App\Domain\Client\Port\ClientAuthTokenPort;
use App\Domain\Client\ValueObject\ClientId;
use App\Infrastructure\Client\Model\CLN_Client;

final class SanctumClientAuthTokenAdapter implements ClientAuthTokenPort
{
    public function issueToken(ClientId $clientId): string
    {
        $client = CLN_Client::query()->findOrFail($clientId->value());

        return $client->createToken('client-api')->plainTextToken;
    }
}
