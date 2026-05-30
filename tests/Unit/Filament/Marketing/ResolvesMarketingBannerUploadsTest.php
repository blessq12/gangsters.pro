<?php

namespace Tests\Unit\Filament\Marketing;

use App\Application\Marketing\Contracts\MarketingMediaStoragePort;
use App\Filament\Marketing\Concerns\ResolvesMarketingBannerUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Tests\TestCase;

final class ResolvesMarketingBannerUploadsTest extends TestCase
{
    public function test_resolve_stores_new_upload_via_port(): void
    {
        $storedPath = 'marketing/banners/stored.jpg';

        $file = $this->createMock(TemporaryUploadedFile::class);

        $port = $this->createMock(MarketingMediaStoragePort::class);
        $port->expects($this->once())
            ->method('store')
            ->with($file, 'marketing/banners')
            ->willReturn($storedPath);

        $this->app->instance(MarketingMediaStoragePort::class, $port);

        $resolver = new class
        {
            use ResolvesMarketingBannerUploads;

            /**
             * @param  array<string, mixed>  $data
             * @param  array{image_mobile: ?string, image_desktop: ?string}  $existing
             * @return array{image_mobile: ?string, image_desktop: ?string}
             */
            public function resolve(array $data, array $existing): array
            {
                return $this->resolveBannerImagePaths($data, $existing);
            }
        };

        $paths = $resolver->resolve(
            ['image_mobile_upload' => $file],
            ['image_mobile' => null, 'image_desktop' => 'marketing/banners/old.jpg'],
        );

        $this->assertSame($storedPath, $paths['image_mobile']);
        $this->assertSame('marketing/banners/old.jpg', $paths['image_desktop']);
    }

    public function test_resolve_keeps_existing_when_no_new_file(): void
    {
        $port = $this->createMock(MarketingMediaStoragePort::class);
        $port->expects($this->never())->method('store');

        $this->app->instance(MarketingMediaStoragePort::class, $port);

        $resolver = new class
        {
            use ResolvesMarketingBannerUploads;

            /**
             * @param  array<string, mixed>  $data
             * @param  array{image_mobile: ?string, image_desktop: ?string}  $existing
             * @return array{image_mobile: ?string, image_desktop: ?string}
             */
            public function resolve(array $data, array $existing): array
            {
                return $this->resolveBannerImagePaths($data, $existing);
            }
        };

        $paths = $resolver->resolve(
            [],
            [
                'image_mobile' => 'marketing/banners/m.jpg',
                'image_desktop' => 'marketing/banners/d.jpg',
            ],
        );

        $this->assertSame('marketing/banners/m.jpg', $paths['image_mobile']);
        $this->assertSame('marketing/banners/d.jpg', $paths['image_desktop']);
    }

    public function test_resolve_uses_filament_stored_path_without_calling_port(): void
    {
        $port = $this->createMock(MarketingMediaStoragePort::class);
        $port->expects($this->never())->method('store');

        $this->app->instance(MarketingMediaStoragePort::class, $port);

        $resolver = new class
        {
            use ResolvesMarketingBannerUploads;

            /**
             * @param  array<string, mixed>  $data
             * @param  array{image_mobile: ?string, image_desktop: ?string}  $existing
             * @return array{image_mobile: ?string, image_desktop: ?string}
             */
            public function resolve(array $data, array $existing): array
            {
                return $this->resolveBannerImagePaths($data, $existing);
            }
        };

        $paths = $resolver->resolve(
            ['image_mobile_upload' => ['marketing/banners/new.png']],
            ['image_mobile' => null, 'image_desktop' => 'marketing/banners/d.jpg'],
        );

        $this->assertSame('marketing/banners/new.png', $paths['image_mobile']);
        $this->assertSame('marketing/banners/d.jpg', $paths['image_desktop']);
    }

    public function test_resolve_rejects_path_outside_marketing_directory(): void
    {
        $port = $this->createMock(MarketingMediaStoragePort::class);
        $port->expects($this->never())->method('store');

        $this->app->instance(MarketingMediaStoragePort::class, $port);

        $resolver = new class
        {
            use ResolvesMarketingBannerUploads;

            /**
             * @param  array<string, mixed>  $data
             * @param  array{image_mobile: ?string, image_desktop: ?string}  $existing
             * @return array{image_mobile: ?string, image_desktop: ?string}
             */
            public function resolve(array $data, array $existing): array
            {
                return $this->resolveBannerImagePaths($data, $existing);
            }
        };

        $paths = $resolver->resolve(
            ['image_mobile_upload' => ['../etc/passwd']],
            ['image_mobile' => 'marketing/banners/keep.jpg', 'image_desktop' => null],
        );

        $this->assertSame('marketing/banners/keep.jpg', $paths['image_mobile']);
    }
}
