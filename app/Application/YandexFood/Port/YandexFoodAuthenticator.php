<?php

namespace App\Application\YandexFood\Port;

interface YandexFoodAuthenticator
{
    public function assertEnabled(): void;

    public function authenticateClient(?string $clientId, ?string $clientSecret): void;

    public function authenticateBearer(?string $token): void;

    public function accessToken(): string;
}
