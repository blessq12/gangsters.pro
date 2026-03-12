<?php

namespace App\Application\Client\DTO;

final class RequestPasswordResetDTO
{
    public function __construct(
        public readonly string $email,
    ) {
    }
}

