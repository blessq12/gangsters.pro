<?php

namespace App\Application\Client\DTO;

final readonly class ChangePasswordWithTokenDto
{
    public function __construct(
        public string $token,
        public string $password,
    ) {}
}
