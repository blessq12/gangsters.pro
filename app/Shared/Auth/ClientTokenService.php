<?php

namespace App\Shared\Auth;

interface ClientTokenService
{
    public function issueTokenForClient(int $clientId): string;

    public function getClientIdFromToken(?string $bearerToken): ?int;
}

