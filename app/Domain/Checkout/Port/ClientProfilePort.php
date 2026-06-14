<?php

namespace App\Domain\Checkout\Port;

interface ClientProfilePort
{
    public function findRegisteredProfile(int $clientId): ?RegisteredClientProfileQuote;
}
