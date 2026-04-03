<?php

namespace App\Application\Client\DTO;

final class RegisterDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly string $email,
        public readonly ?string $birthDate, // ISO date or null, парсится в use-case
        public readonly ?string $password,
        public readonly bool $consentPersonalData,
        public readonly bool $consentMarketing,
    ) {}
}
