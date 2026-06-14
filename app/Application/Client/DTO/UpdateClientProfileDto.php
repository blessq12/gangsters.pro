<?php

namespace App\Application\Client\DTO;

final readonly class UpdateClientProfileDto
{
    public function __construct(
        public int $clientId,
        public string $name,
        public string $phone,
        public ?string $email,
        public ?string $birthDate,
        public ?bool $consentPersonalData,
        public ?bool $consentMarketing,
    ) {}
}
