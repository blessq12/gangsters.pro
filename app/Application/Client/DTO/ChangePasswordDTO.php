<?php

namespace App\Application\Client\DTO;

final class ChangePasswordDTO
{
    public function __construct(
        public readonly string $token,
        public readonly string $password,
    ) {
    }
}

