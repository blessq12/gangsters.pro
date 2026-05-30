<?php

namespace App\Application\Marketing\Promotion\DTO;

final readonly class SavePromotionDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?string $image,
    ) {}
}
