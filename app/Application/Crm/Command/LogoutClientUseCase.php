<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Support\ClientAccessToken;
use App\Infrastructure\Crm\Model\CRM_Client;

final class LogoutClientUseCase
{
    /**
     * @return array{ok: true}
     */
    public function execute(?CRM_Client $client): array
    {
        ClientAccessToken::revokeCurrent($client);

        return ['ok' => true];
    }
}
