<?php

namespace App\Application\Client\Command;

use App\Application\Client\Support\ClientAccessToken;
use App\Infrastructure\Client\Model\ClientModel;

final class LogoutClientUseCase
{
    /**
     * @return array{ok: true}
     */
    public function execute(?ClientModel $client): array
    {
        ClientAccessToken::revokeCurrent($client);

        return ['ok' => true];
    }
}
