<?php

namespace App\Infrastructure\AggregatorIngress\Auth;

use App\Application\AggregatorIngress\Port\IngressPartnerAuthenticator;
use App\Domain\AggregatorIngress\Exception\IngressAuthenticationFailedException;
use App\Domain\AggregatorIngress\Exception\PartnerNotConfiguredException;

final class ConfigIngressPartnerAuthenticator implements IngressPartnerAuthenticator
{
    public function supports(string $partnerCode): bool
    {
        return $this->partnerConfig($partnerCode) !== null;
    }

    public function authenticate(string $partnerCode, ?string $apiKey): void
    {
        $config = $this->partnerConfig($partnerCode);

        if ($config === null || ($config['enabled'] ?? false) !== true) {
            throw new PartnerNotConfiguredException($partnerCode);
        }

        $expectedKey = (string) ($config['api_key'] ?? '');
        if ($expectedKey === '' || ! is_string($apiKey) || ! hash_equals($expectedKey, $apiKey)) {
            throw new IngressAuthenticationFailedException();
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function partnerConfig(string $partnerCode): ?array
    {
        $partners = config('ingress.partners', []);
        if (! is_array($partners)) {
            return null;
        }

        $config = $partners[$partnerCode] ?? null;

        return is_array($config) ? $config : null;
    }
}
