<?php

namespace App\Domain\Order\Port;

interface ClientProfilePort
{
    public function findRegisteredProfile(int $clientId): ?RegisteredClientProfileQuote;
}
