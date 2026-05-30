<?php

namespace Tests\Unit\Application\Marketing;

use App\Infrastructure\Marketing\Storage\LocalMarketingMediaStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class LocalMarketingMediaStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
    }

    public function test_store_returns_path_under_directory(): void
    {
        $storage = new LocalMarketingMediaStorage;
        $file = UploadedFile::fake()->image('banner.jpg');

        $path = $storage->store($file, 'marketing/banners');

        $this->assertStringStartsWith('marketing/banners/', $path);
        Storage::disk('media')->assertExists($path);
    }

    public function test_delete_removes_file(): void
    {
        $storage = new LocalMarketingMediaStorage;
        $path = 'marketing/banners/test.jpg';
        Storage::disk('media')->put($path, 'content');

        $storage->delete($path);

        Storage::disk('media')->assertMissing($path);
    }
}
