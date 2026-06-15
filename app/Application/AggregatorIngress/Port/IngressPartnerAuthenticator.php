<?php

namespace App\Application\AggregatorIngress\Port;

interface IngressPartnerAuthenticator
{
    public function supports(string $partnerCode): bool;

    public function authenticate(string $partnerCode, ?string $apiKey): void;
}
