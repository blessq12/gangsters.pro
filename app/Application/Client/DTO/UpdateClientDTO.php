<?php

namespace App\Application\Client\DTO;

final class UpdateClientDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $birthDate,
        public readonly ?bool $consentPersonalData,
        public readonly ?bool $consentMarketing,
    ) {
    }
}

