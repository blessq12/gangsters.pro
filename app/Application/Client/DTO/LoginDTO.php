<?php

namespace App\Application\Client\DTO;

final class LoginDTO
{
    public function __construct(
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly string $password,
    ) {
    }
}

