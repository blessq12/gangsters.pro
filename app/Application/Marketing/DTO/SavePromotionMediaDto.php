<?php

namespace App\Application\Marketing\DTO;

final readonly class SavePromotionMediaDto
{
    public function __construct(
        public ?int $id,
        public string $title,
        public ?string $body,
        public ?string $image,
        public bool $isActive,
    ) {}
}
