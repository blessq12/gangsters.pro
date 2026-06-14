<?php

namespace App\Domain\Client\Port;

interface ClientPasswordResetNotifierPort
{
    public function notify(string $email, string $plainToken): void;
}
