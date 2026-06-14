<?php

namespace App\Application\Client\DTO;

final readonly class RegisterClientDto
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $email,
        public ?string $birthDate,
        public string $password,
        public bool $consentPersonalData,
        public bool $consentMarketing,
    ) {}
}
