<?php

namespace App\Application\Marketing\Command;

use App\Application\Marketing\DTO\SaveBannerDto;
use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Supporting\Promotion\Repository\BannerRepository;
use App\Infrastructure\Marketing\Support\MarketingMediaStore;
use App\Infrastructure\Marketing\Support\MarketingStoredMedia;
use Illuminate\Support\Facades\DB;

final class SaveBannerUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly MarketingPresenter $presenter,
        private readonly MarketingMediaStore $media,
    ) {}

    /**
     * @return array{banner: array<string, mixed>, created: bool}
     */
    public function execute(SaveBannerDto $dto): array
    {
        $isCreate = $dto->id === null;
        if ($isCreate && ($dto->imageDesktopFile === null || $dto->imageMobileFile === null)) {
            throw new \InvalidArgumentException('Нужны image_desktop и image_mobile.');
        }

        $storedPaths = [];

        try {
            $banner = DB::transaction(function () use ($dto, $isCreate, &$storedPaths) {
                $previous = $dto->id !== null ? $this->banners->findStoredPaths($dto->id) : null;

                $banner = $this->banners->save(
                    id: $dto->id,
                    isActive: $dto->isActive,
                    desktopPath: $isCreate ? '' : null,
                    mobilePath: $isCreate ? '' : null,
                );

                $desktopPath = null;
                $mobilePath = null;

                if ($dto->imageDesktopFile !== null) {
                    $desktopPath = $this->media->storeBannerImage(
                        $banner->id(),
                        $dto->imageDesktopFile,
                        'desktop',
                    );
                    $storedPaths[] = $desktopPath;
                }

                if ($dto->imageMobileFile !== null) {
                    $mobilePath = $this->media->storeBannerImage(
                        $banner->id(),
                        $dto->imageMobileFile,
                        'mobile',
                    );
                    $storedPaths[] = $mobilePath;
                }

                if ($desktopPath !== null || $mobilePath !== null) {
                    $banner = $this->banners->save(
                        id: $banner->id(),
                        isActive: $dto->isActive,
                        desktopPath: $desktopPath,
                        mobilePath: $mobilePath,
                    );
                }

                if ($previous !== null) {
                    if ($desktopPath !== null) {
                        MarketingStoredMedia::deleteIfStored($previous['image_desktop']);
                    }
                    if ($mobilePath !== null) {
                        MarketingStoredMedia::deleteIfStored($previous['image_mobile']);
                    }
                }

                return $banner;
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                MarketingStoredMedia::deleteIfStored($path);
            }
            throw $exception;
        }

        return [
            'banner' => $this->presenter->banner($banner),
            'created' => $isCreate,
        ];
    }
}
