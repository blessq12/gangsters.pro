<?php

namespace App\Application\Company\DTO;

final readonly class SaveBranchDto
{
    public function __construct(
        public ?int $id,
        public int $companyId,
        public string $name,
        public bool $isActive,
    ) {}
}
