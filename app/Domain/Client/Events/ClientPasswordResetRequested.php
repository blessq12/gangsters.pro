<?php

namespace App\Domain\Client\Events;

use App\Shared\Events\DomainEvent;

final class ClientPasswordResetRequested implements DomainEvent
{
    public function __construct(
        private readonly string $email,
        private readonly string $resetToken,
    ) {
    }

    public function email(): string
    {
        return $this->email;
    }

    public function resetToken(): string
    {
        return $this->resetToken;
    }
}
