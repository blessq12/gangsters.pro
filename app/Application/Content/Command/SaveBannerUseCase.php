<?php

namespace App\Application\Content\Command;

use App\Domain\Content\Repository\BannerRepository;

final class SaveBannerUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(?int $id, bool $isActive, ?string $desktopPath, ?string $mobilePath): array
    {
        $banner = $this->banners->save($id, $isActive, $desktopPath, $mobilePath);

        return [
            'id' => $banner->id(),
            'image_desktop' => $banner->imageDesktop(),
            'image_mobile' => $banner->imageMobile(),
            'is_active' => $banner->isActive(),
        ];
    }
}
