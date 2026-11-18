<?php

namespace App\Application\Crm\DTO;

final readonly class UpdateClientProfileDto
{
    public function __construct(
        public ?string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $birthDate,
        public ?bool $consentMarketing,
    ) {}
}
