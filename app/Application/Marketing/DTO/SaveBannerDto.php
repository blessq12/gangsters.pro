<?php

namespace App\Application\Marketing\DTO;

use Illuminate\Http\UploadedFile;

final readonly class SaveBannerDto
{
    public function __construct(
        public ?int $id,
        public bool $isActive,
        public ?UploadedFile $imageDesktopFile,
        public ?UploadedFile $imageMobileFile,
    ) {}
}
