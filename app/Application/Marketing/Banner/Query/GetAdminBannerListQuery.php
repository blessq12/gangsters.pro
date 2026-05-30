<?php

namespace App\Application\Marketing\Banner\Query;

use App\Domain\SystemContent\Entity\Banner;
use App\Domain\SystemContent\Repository\BannerRepository;

final class GetAdminBannerListQuery
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return array_map(
            static fn (Banner $banner): array => [
                'id' => $banner->id(),
                'title' => $banner->title(),
                'description' => $banner->description(),
                'image' => $banner->imagePath(),
                'image_mobile' => $banner->imageMobilePath(),
                'image_desktop' => $banner->imageDesktopPath(),
            ],
            $this->banners->findAllOrdered(),
        );
    }
}
