<?php

namespace App\Infrastructure\Crm\Port;

use App\Domain\Crm\Port\ClientAccessTokenIssuer;
use App\Infrastructure\Crm\Model\CRM_Client;

final class SanctumClientAccessTokenIssuer implements ClientAccessTokenIssuer
{
    public function issue(int $clientId, string $tokenName = 'spa'): string
    {
        $client = CRM_Client::query()->find($clientId);
        if (! $client instanceof CRM_Client) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        return $client->createToken($tokenName)->plainTextToken;
    }
}
