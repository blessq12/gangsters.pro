<?php

namespace App\Application\Operations\Client\DTO;

final readonly class CreateAdminClientDTO
{
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $email,
        public ?string $birthDate,
        public ?string $password,
        public bool $consentPersonalData,
        public bool $consentMarketing,
    ) {}
}
