<?php

namespace App\Application\Marketing\DTO;

final readonly class SaveBannerDto
{
    public function __construct(
        public ?int $id,
        public bool $isActive,
        public ?string $imageDesktop,
        public ?string $imageMobile,
    ) {}
}
