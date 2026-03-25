<?php

namespace App\Application\SystemContent\Query;

use App\Domain\SystemContent\Entity\Banner;
use App\Domain\SystemContent\Repository\BannerRepository;
use App\Shared\SystemContent\MediaUrlResolver;

final class GetSystemBannersUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {
    }

    public function execute(): array
    {
        $items = $this->banners->findAllOrdered();

        return [
            'data' => array_map(
                fn (Banner $banner) => [
                    'id' => $banner->id(),
                    'title' => $banner->title(),
                    'description' => $banner->description(),
                    // legacy поле (оставляем для обратной совместимости)
                    'image' => $this->mediaUrlResolver->resolve($banner->imagePath()),
                    // новые поля под разные устройства
                    'image_mobile' => $this->mediaUrlResolver->resolve(
                        $banner->imageMobilePath() ?? $banner->imagePath(),
                    ),
                    'image_desktop' => $this->mediaUrlResolver->resolve(
                        $banner->imageDesktopPath() ?? $banner->imagePath(),
                    ),
                ],
                $items,
            ),
        ];
    }
}

