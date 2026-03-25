<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Banner as BannerEntity;
use App\Domain\SystemContent\Repository\BannerRepository;
use App\Infrastructure\SystemContent\Model\SYS_Banner;

final class EloquentBannerRepository implements BannerRepository
{
    public function findAllOrdered(): array
    {
        return SYS_Banner::query()
            ->orderBy('id')
            ->get()
            ->map(
                static fn (SYS_Banner $banner) => new BannerEntity(
                    id: (int) $banner->id,
                    title: $banner->title,
                    description: $banner->description,
                    imagePath: $banner->image,
                    imageMobilePath: $banner->image_mobile ?? null,
                    imageDesktopPath: $banner->image_desktop ?? null,
                ),
            )
            ->all();
    }
}

