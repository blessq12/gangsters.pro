<?php

namespace App\Application\Marketing\Command;

use App\Application\Marketing\DTO\SaveBannerDto;
use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Marketing\Repository\BannerRepository;

final class SaveBannerUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly MarketingPresenter $presenter,
    ) {}

    /**
     * @return array{banner: array<string, mixed>, created: bool}
     */
    public function execute(SaveBannerDto $dto): array
    {
        $desktop = $this->nullablePath($dto->imageDesktop);
        $mobile = $this->nullablePath($dto->imageMobile);

        if ($dto->id === null && ($desktop === null || $mobile === null)) {
            throw new \InvalidArgumentException('Нужны image_desktop и image_mobile.');
        }

        $banner = $this->banners->save(
            id: $dto->id,
            isActive: $dto->isActive,
            desktopPath: $desktop,
            mobilePath: $mobile,
        );

        return [
            'banner' => $this->presenter->banner($banner),
            'created' => $dto->id === null,
        ];
    }

    private function nullablePath(?string $path): ?string
    {
        $trimmed = trim((string) ($path ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
