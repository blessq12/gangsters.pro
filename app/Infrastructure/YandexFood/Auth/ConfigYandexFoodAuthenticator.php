<?php

namespace App\Infrastructure\YandexFood\Auth;

use App\Application\YandexFood\Port\YandexFoodAuthenticator;
use App\Domain\YandexFood\Exception\YandexFoodBearerTokenRejectedException;
use App\Domain\YandexFood\Exception\YandexFoodDisabledException;
use App\Domain\YandexFood\Exception\YandexFoodOAuthRejectedException;

final class ConfigYandexFoodAuthenticator implements YandexFoodAuthenticator
{
    public function assertEnabled(): void
    {
        if (! $this->isEnabled()) {
            throw new YandexFoodDisabledException();
        }
    }

    public function authenticateClient(?string $clientId, ?string $clientSecret): void
    {
        $this->assertEnabled();

        if ($clientId === null || $clientId === '' || $clientSecret === null || $clientSecret === '') {
            throw new YandexFoodOAuthRejectedException('Client ID and Client Secret are required');
        }

        $expectedClientId = $this->configString('client_id');
        $expectedClientSecret = $this->configString('client_secret');

        if ($expectedClientId === '' || $expectedClientSecret === '') {
            throw new YandexFoodOAuthRejectedException('Client ID or Client Secret are not set in app config');
        }

        if (! hash_equals($expectedClientId, $clientId) || ! hash_equals($expectedClientSecret, $clientSecret)) {
            throw new YandexFoodOAuthRejectedException('Invalid client ID or client secret');
        }
    }

    public function authenticateBearer(?string $token): void
    {
        $this->assertEnabled();

        $expectedToken = $this->configString('auth_token');

        if ($expectedToken === '' || ! is_string($token) || ! hash_equals($expectedToken, $token)) {
            throw new YandexFoodBearerTokenRejectedException();
        }
    }

    public function accessToken(): string
    {
        $this->assertEnabled();

        $token = $this->configString('auth_token');
        if ($token === '') {
            throw new YandexFoodOAuthRejectedException('Client ID or Client Secret are not set in app config');
        }

        return $token;
    }

    private function isEnabled(): bool
    {
        return (bool) config('yandex_food.enabled', false);
    }

    private function configString(string $key): string
    {
        $value = config('yandex_food.'.$key);

        return is_string($value) ? $value : '';
    }
}
