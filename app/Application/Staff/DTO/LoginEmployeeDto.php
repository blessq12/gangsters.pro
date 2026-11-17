<?php

namespace App\Application\Staff\DTO;

final readonly class LoginEmployeeDto
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
