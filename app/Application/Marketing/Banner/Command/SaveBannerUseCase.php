<?php

namespace App\Application\Marketing\Banner\Command;

use App\Domain\SystemContent\Entity\Banner;
use App\Domain\SystemContent\Repository\BannerRepository;

final class SaveBannerUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(array $data): array
    {
        $id = (int) ($data['id'] ?? 0);

        $banner = new Banner(
            id: $id,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            imagePath: $data['image'] ?? null,
            imageMobilePath: $data['image_mobile'] ?? null,
            imageDesktopPath: $data['image_desktop'] ?? null,
        );

        $saved = $this->banners->save($banner);

        return [
            'id' => $saved->id(),
            'title' => $saved->title(),
            'description' => $saved->description(),
            'image' => $saved->imagePath(),
            'image_mobile' => $saved->imageMobilePath(),
            'image_desktop' => $saved->imageDesktopPath(),
        ];
    }
}
