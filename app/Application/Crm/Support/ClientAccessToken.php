<?php

namespace App\Application\Crm\Support;

use App\Infrastructure\Crm\Model\CRM_Client;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Выдача/ревокация Sanctum PAT для CRM_Client (портов нет).
 */
final class ClientAccessToken
{
    public static function issue(int $clientId, string $tokenName = 'client'): string
    {
        $model = CRM_Client::query()->find($clientId);
        if (! $model instanceof CRM_Client) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        return $model->createToken($tokenName)->plainTextToken;
    }

    public static function revokeCurrent(?CRM_Client $client): void
    {
        if (! $client instanceof CRM_Client) {
            return;
        }

        $token = $client->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
