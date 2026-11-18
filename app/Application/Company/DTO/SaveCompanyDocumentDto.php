<?php

namespace App\Application\Company\DTO;

final readonly class SaveCompanyDocumentDto
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $content,
    ) {}
}
