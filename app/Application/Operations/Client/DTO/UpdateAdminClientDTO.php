<?php

namespace App\Application\Operations\Client\DTO;

final readonly class UpdateAdminClientDTO
{
    public function __construct(
        public int $clientId,
        public ?string $name,
        public ?string $email,
        public ?string $birthDate,
        public ?bool $consentPersonalData,
        public ?bool $consentMarketing,
    ) {
    }
}
