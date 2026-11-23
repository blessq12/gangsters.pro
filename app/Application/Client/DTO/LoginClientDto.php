<?php

namespace App\Application\Client\DTO;

final readonly class LoginClientDto
{
    public function __construct(
        public ?string $phone,
        public ?string $email,
        public string $password,
    ) {}
}
