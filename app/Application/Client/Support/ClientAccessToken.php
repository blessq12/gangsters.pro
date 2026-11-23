<?php

namespace App\Application\Client\Support;

use App\Infrastructure\Client\Model\ClientModel;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Выдача/ревокация Sanctum PAT для ClientModel (портов нет).
 */
final class ClientAccessToken
{
    public static function issue(int $clientId, string $tokenName = 'client'): string
    {
        $model = ClientModel::query()->find($clientId);
        if (! $model instanceof ClientModel) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        return $model->createToken($tokenName)->plainTextToken;
    }

    public static function revokeCurrent(?ClientModel $client): void
    {
        if (! $client instanceof ClientModel) {
            return;
        }

        $token = $client->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
