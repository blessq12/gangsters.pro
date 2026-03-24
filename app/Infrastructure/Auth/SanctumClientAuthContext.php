<?php

namespace App\Infrastructure\Auth;

use App\Application\Common\Exceptions\UnauthorizedException;
use App\Shared\Auth\ClientAuthContext;
use App\Infrastructure\Client\Model\UR_Client;
use Illuminate\Contracts\Auth\Factory as AuthManager;

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
            throw new UnauthorizedException();
        }

        return $user->id;
    }
}

