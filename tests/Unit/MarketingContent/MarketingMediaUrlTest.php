<?php

namespace Tests\Unit\MarketingContent;

use App\Infrastructure\MarketingContent\Support\MarketingStoredPath;
use App\Infrastructure\MarketingContent\Support\PublicMediaUrl;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MarketingMediaUrlTest extends TestCase
{
    #[Test]
    public function public_disk_path_даёт_один_storage_в_url(): void
    {
        $url = PublicMediaUrl::resolve('marketing/banners/desktop/foo.png');

        $this->assertSame('/storage/marketing/banners/desktop/foo.png', $url);
    }

    #[Test]
    public function путь_с_дублем_storage_нормализуется(): void
    {
        $url = PublicMediaUrl::resolve('storage/marketing/banners/desktop/foo.png');

        $this->assertSame('/storage/marketing/banners/desktop/foo.png', $url);
    }

    #[Test]
    public function статика_сидера_через_asset(): void
    {
        $url = PublicMediaUrl::resolve('/images/banners/banner1.jpeg');

        $this->assertStringEndsWith('/images/banners/banner1.jpeg', $url);
    }

    #[Test]
    public function filament_state_для_upload_это_disk_relative(): void
    {
        $state = MarketingStoredPath::filamentImageState('marketing/banners/desktop/foo.png');

        $this->assertSame('marketing/banners/desktop/foo.png', $state);
    }

    #[Test]
    public function filament_state_для_сидера_это_абсолютный_url(): void
    {
        $state = MarketingStoredPath::filamentImageState('/images/banners/banner1.jpeg');

        $this->assertNotNull($state);
        $this->assertNotFalse(filter_var($state, FILTER_VALIDATE_URL));
    }
}
