<?php

namespace App\Infrastructure\Auth;

use App\Domain\Shared\Auth\ClientAuthContext;
use App\Infrastructure\Client\Model\UR_Client;
use Illuminate\Contracts\Auth\Factory as AuthManager;
use LogicException;

final class SanctumClientAuthContext implements ClientAuthContext
{
    public function __construct(
        private readonly AuthManager $auth,
    ) {
    }

    public function currentClientId(): int
    {
        $user = $this->auth->guard('sanctum')->user();

        if (!$user instanceof UR_Client) {
            throw new LogicException('Unauthenticated');
        }

        return $user->id;
    }
}

