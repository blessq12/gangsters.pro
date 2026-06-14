<?php

namespace App\Domain\Client\Port;

interface ClientPasswordResetTokenStorePort
{
    public function store(string $email, string $plainToken): void;

    public function resolveEmailByToken(string $plainToken): ?string;

    public function delete(string $email): void;
}
