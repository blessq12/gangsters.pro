<?php

namespace App\Application\Catalog\DTO;

final readonly class SaveSetDto
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $sku,
        public int $price,
        public string $status,
    ) {}
}
