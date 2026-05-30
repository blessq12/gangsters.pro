<?php

namespace App\Application\Marketing\Banner\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Banner\DTO\SaveBannerDTO;
use App\Application\Marketing\Banner\Presenter\AdminBannerPresenter;
use App\Domain\SystemContent\Entity\Banner;
use App\Domain\SystemContent\Repository\BannerRepository;

final class SaveBannerUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly AdminBannerPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBannerDTO $dto): array
    {
        $existing = $dto->id > 0 ? $this->banners->findById($dto->id) : null;

        if ($dto->id > 0 && $existing === null) {
            throw new ApiException('Banner not found.', 404);
        }

        $legacyImage = $this->resolveLegacyImagePath($dto, $existing);

        if ($legacyImage === null || $legacyImage === '') {
            throw new ApiException('Укажите изображения для mobile и desktop.', 422);
        }

        $banner = new Banner(
            id: $dto->id,
            title: $dto->title,
            description: $dto->description,
            imagePath: $legacyImage,
            imageMobilePath: $dto->imageMobile,
            imageDesktopPath: $dto->imageDesktop,
        );

        $saved = $this->banners->save($banner);

        return $this->presenter->presentDetail($saved);
    }

    private function resolveLegacyImagePath(SaveBannerDTO $dto, ?Banner $existing): ?string
    {
        $fromDto = $dto->imageMobile ?? $dto->imageDesktop;

        if ($fromDto !== null && $fromDto !== '') {
            return $fromDto;
        }

        return $existing?->imagePath();
    }
}
