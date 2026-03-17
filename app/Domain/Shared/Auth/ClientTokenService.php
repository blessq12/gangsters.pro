<?php

namespace App\Domain\Shared\Auth;

interface ClientTokenService
{
    public function issueTokenForClient(int $clientId): string;

    public function getClientIdFromToken(?string $bearerToken): ?int;
}

