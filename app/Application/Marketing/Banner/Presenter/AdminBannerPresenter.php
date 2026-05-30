<?php

namespace App\Application\Marketing\Banner\Presenter;

use App\Domain\SystemContent\Entity\Banner;
use App\Shared\SystemContent\MediaUrlResolver;

final class AdminBannerPresenter
{
    public function __construct(
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function presentListItem(Banner $banner): array
    {
        return $this->presentDetail($banner);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentDetail(Banner $banner): array
    {
        $mobilePath = $banner->imageMobilePath() ?? $banner->imagePath();
        $desktopPath = $banner->imageDesktopPath() ?? $banner->imagePath();
        $previewPath = $mobilePath ?? $desktopPath ?? $banner->imagePath();

        return [
            'id' => $banner->id(),
            'title' => $banner->title(),
            'description' => $banner->description(),
            'image' => $banner->imagePath(),
            'image_mobile' => $banner->imageMobilePath(),
            'image_desktop' => $banner->imageDesktopPath(),
            'image_url' => $this->mediaUrlResolver->resolve($previewPath),
            'image_mobile_url' => $this->mediaUrlResolver->resolve($mobilePath),
            'image_desktop_url' => $this->mediaUrlResolver->resolve($desktopPath),
        ];
    }
}
