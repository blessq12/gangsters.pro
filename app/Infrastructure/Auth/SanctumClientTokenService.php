<?php

namespace App\Infrastructure\Auth;

use App\Shared\Auth\ClientTokenService;
use App\Infrastructure\Client\Model\UR_Client;
use Laravel\Sanctum\PersonalAccessToken;

final class SanctumClientTokenService implements ClientTokenService
{
    public function issueTokenForClient(int $clientId): string
    {
        $model = UR_Client::findOrFail($clientId);

        return $model->createToken('client')->plainTextToken;
    }

    public function getClientIdFromToken(?string $bearerToken): ?int
    {
        if ($bearerToken === null || $bearerToken === '') {
            return null;
        }

        /** @var PersonalAccessToken|null $accessToken */
        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if ($accessToken === null) {
            return null;
        }

        $tokenable = $accessToken->tokenable;

        if (!$tokenable instanceof UR_Client) {
            return null;
        }

        return $tokenable->id;
    }
}

