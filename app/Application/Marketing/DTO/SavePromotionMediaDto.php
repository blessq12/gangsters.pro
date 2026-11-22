<?php

namespace App\Application\Marketing\DTO;

use Illuminate\Http\UploadedFile;

final readonly class SavePromotionMediaDto
{
    public function __construct(
        public ?int $id,
        public string $title,
        public ?string $body,
        public bool $isActive,
        public ?UploadedFile $imageFile,
        public bool $removeImage = false,
    ) {}
}
