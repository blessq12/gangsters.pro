<?php

namespace App\Application\Marketing\Banner\DTO;

final readonly class SaveBannerDTO
{
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $description,
        public ?string $imageMobile,
        public ?string $imageDesktop,
    ) {}
}
