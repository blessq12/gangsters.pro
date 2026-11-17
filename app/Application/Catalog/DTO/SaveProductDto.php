<?php

namespace App\Application\Catalog\DTO;

final readonly class SaveProductDto
{
    public function __construct(
        public ?int $id,
        public string $name,
        public int $price,
        public ?int $weight,
        public string $status,
        public ?string $description,
        public ?string $sku,
    ) {}
}
