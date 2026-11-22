<?php

namespace App\Application\Staff\DTO;

final readonly class SaveEmployeeDto
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public ?string $phone,
        public string $role,
        public ?string $password,
        public ?bool $isActive,
        public ?int $branchId = null,
    ) {}
}
