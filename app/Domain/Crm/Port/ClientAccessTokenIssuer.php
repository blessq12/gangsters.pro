<?php

namespace App\Domain\Crm\Port;

interface ClientAccessTokenIssuer
{
    public function issue(int $clientId, string $tokenName = 'spa'): string;
}
