<?php

namespace Tests\Feature\Admin;

use App\Application\Marketing\Banner\Command\SaveBannerUseCase;
use App\Application\Marketing\Banner\DTO\SaveBannerDTO;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Api\ApiTestCase;

final class MarketingBannerSaveApiSmokeTest extends ApiTestCase
{
    private ?int $bannerId = null;

    protected function tearDown(): void
    {
        if ($this->bannerId !== null) {
            SYS_Banner::query()->whereKey($this->bannerId)->delete();
            $this->bannerId = null;
        }

        parent::tearDown();
    }

    public function test_save_banner_dto_exposes_urls_in_system_api(): void
    {
        $this->skipUnlessTablesExist(['banners']);

        Storage::fake('media');
        $mobilePath = 'marketing/banners/smoke-mobile.png';
        $desktopPath = 'marketing/banners/smoke-desktop.png';
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            true,
        ) ?: '';
        Storage::disk('media')->put($mobilePath, $png);
        Storage::disk('media')->put($desktopPath, $png);

        $saved = app(SaveBannerUseCase::class)->execute(new SaveBannerDTO(
            id: 0,
            title: 'Smoke banner',
            description: null,
            imageMobile: $mobilePath,
            imageDesktop: $desktopPath,
        ));

        $this->bannerId = (int) $saved['id'];

        $response = $this->getJson('/api/system/banners');
        $response->assertOk();

        $found = collect($response->json('data'))->firstWhere('id', $this->bannerId);
        $this->assertNotNull($found);
        $this->assertIsString($found['image']);
        $this->assertStringContainsString($mobilePath, $found['image']);
        $this->assertStringContainsString($mobilePath, $found['image_mobile']);
        $this->assertStringContainsString($desktopPath, $found['image_desktop']);
    }
}
