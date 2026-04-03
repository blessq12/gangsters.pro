<?php

namespace App\Application\Client\Ports;

interface ClientPasswordResetMailer
{
    public function sendResetLink(string $email, string $token): void;
}
