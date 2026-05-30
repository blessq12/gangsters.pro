<?php

namespace Tests\Unit\Application\Marketing;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Banner\Command\SaveBannerUseCase;
use App\Application\Marketing\Banner\DTO\SaveBannerDTO;
use App\Application\Marketing\Banner\Presenter\AdminBannerPresenter;
use App\Domain\SystemContent\Entity\Banner;
use App\Domain\SystemContent\Repository\BannerRepository;
use App\Shared\SystemContent\MediaUrlResolver;
use PHPUnit\Framework\TestCase;

final class SaveBannerUseCaseTest extends TestCase
{
    public function test_execute_saves_banner_syncs_legacy_image_and_returns_urls(): void
    {
        $saved = new Banner(
            id: 5,
            title: 'Promo',
            description: 'Desc',
            imagePath: 'marketing/banners/mobile.jpg',
            imageMobilePath: 'marketing/banners/mobile.jpg',
            imageDesktopPath: 'marketing/banners/desktop.jpg',
        );

        $repo = new class($saved) implements BannerRepository
        {
            public function __construct(private Banner $banner) {}

            public function findAllOrdered(): array
            {
                return [];
            }

            public function findById(int $id): ?Banner
            {
                return null;
            }

            public function save(Banner $banner): Banner
            {
                return new Banner(
                    id: 5,
                    title: $banner->title(),
                    description: $banner->description(),
                    imagePath: $banner->imagePath(),
                    imageMobilePath: $banner->imageMobilePath(),
                    imageDesktopPath: $banner->imageDesktopPath(),
                );
            }

            public function delete(int $id): void {}
        };

        $presenter = new AdminBannerPresenter(new class implements MediaUrlResolver
        {
            public function resolve(?string $path): ?string
            {
                return $path !== null ? '/storage/'.$path : null;
            }
        });

        $useCase = new SaveBannerUseCase($repo, $presenter);

        $result = $useCase->execute(new SaveBannerDTO(
            id: 0,
            title: 'Promo',
            description: 'Desc',
            imageMobile: 'marketing/banners/mobile.jpg',
            imageDesktop: 'marketing/banners/desktop.jpg',
        ));

        $this->assertSame(5, $result['id']);
        $this->assertSame('/storage/marketing/banners/mobile.jpg', $result['image_url']);
        $this->assertSame('/storage/marketing/banners/mobile.jpg', $result['image_mobile_url']);
        $this->assertSame('/storage/marketing/banners/desktop.jpg', $result['image_desktop_url']);
    }

    public function test_execute_throws_when_no_images_on_create(): void
    {
        $repo = new class implements BannerRepository
        {
            public function findAllOrdered(): array
            {
                return [];
            }

            public function findById(int $id): ?Banner
            {
                return null;
            }

            public function save(Banner $banner): Banner
            {
                return $banner;
            }

            public function delete(int $id): void {}
        };

        $presenter = new AdminBannerPresenter(new class implements MediaUrlResolver
        {
            public function resolve(?string $path): ?string
            {
                return null;
            }
        });

        $useCase = new SaveBannerUseCase($repo, $presenter);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Укажите изображения для mobile и desktop.');

        $useCase->execute(new SaveBannerDTO(
            id: 0,
            title: 'Promo',
            description: null,
            imageMobile: null,
            imageDesktop: null,
        ));
    }

    public function test_execute_update_keeps_legacy_image_when_paths_unchanged(): void
    {
        $existing = new Banner(
            id: 3,
            title: 'Old',
            description: null,
            imagePath: 'marketing/banners/legacy.jpg',
            imageMobilePath: null,
            imageDesktopPath: null,
        );

        $repo = new class($existing) implements BannerRepository
        {
            public function __construct(private Banner $existing) {}

            public function findAllOrdered(): array
            {
                return [];
            }

            public function findById(int $id): ?Banner
            {
                return $this->existing;
            }

            public function save(Banner $banner): Banner
            {
                return $banner;
            }

            public function delete(int $id): void {}
        };

        $presenter = new AdminBannerPresenter(new class implements MediaUrlResolver
        {
            public function resolve(?string $path): ?string
            {
                return $path !== null ? '/storage/'.$path : null;
            }
        });

        $useCase = new SaveBannerUseCase($repo, $presenter);

        $result = $useCase->execute(new SaveBannerDTO(
            id: 3,
            title: 'Updated',
            description: null,
            imageMobile: null,
            imageDesktop: null,
        ));

        $this->assertSame('marketing/banners/legacy.jpg', $result['image']);
    }
}
