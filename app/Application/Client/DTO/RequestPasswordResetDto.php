<?php

namespace App\Application\Client\DTO;

final readonly class RequestPasswordResetDto
{
    public function __construct(
        public string $email,
    ) {}
}
