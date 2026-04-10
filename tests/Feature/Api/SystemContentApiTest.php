<?php

namespace Tests\Feature\Api;

use App\Infrastructure\SystemContent\Model\SYS_Banner;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SystemContentApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipUnlessTablesExist(['banners', 'promotions']);
    }

    private ?int $bannerId = null;

    private ?int $promotionId = null;

    protected function tearDown(): void
    {
        if ($this->bannerId !== null) {
            SYS_Banner::query()->whereKey($this->bannerId)->delete();
            $this->bannerId = null;
        }
        if ($this->promotionId !== null) {
            SYS_Promotion::query()->whereKey($this->promotionId)->delete();
            $this->promotionId = null;
        }

        parent::tearDown();
    }

    public function test_banners_200_contract_with_real_file(): void
    {
        $dir = 'test-uploads/'.Str::uuid();
        $path = $dir.'/banner.png';
        Storage::disk('media')->put($path, $this->minimalPng());

        $banner = SYS_Banner::query()->create([
            'title' => 'PHPUnit banner',
            'description' => 'Описание',
            'image' => $path,
            ...(Schema::hasColumn('banners', 'image_mobile') ? ['image_mobile' => $path] : []),
            ...(Schema::hasColumn('banners', 'image_desktop') ? ['image_desktop' => $path] : []),
        ]);
        $this->bannerId = $banner->id;

        $response = $this->getJson('/api/system/banners');
        $response->assertOk();

        $list = $response->json('data');
        $this->assertIsArray($list);

        $found = null;
        foreach ($list as $row) {
            if ((int) ($row['id'] ?? 0) === (int) $banner->id) {
                $found = $row;
                break;
            }
        }

        $this->assertNotNull($found, 'Созданный баннер должен быть в ответе');
        $this->assertArrayHasKey('id', $found);
        $this->assertArrayHasKey('title', $found);
        $this->assertArrayHasKey('description', $found);
        $this->assertArrayHasKey('image', $found);
        $this->assertIsString($found['image']);
        $this->assertStringContainsString($path, $found['image']);

        if (Schema::hasColumn('banners', 'image_mobile')) {
            $this->assertArrayHasKey('image_mobile', $found);
            $this->assertIsString($found['image_mobile']);
            $this->assertStringContainsString($path, $found['image_mobile']);
        }
        if (Schema::hasColumn('banners', 'image_desktop')) {
            $this->assertArrayHasKey('image_desktop', $found);
            $this->assertIsString($found['image_desktop']);
            $this->assertStringContainsString($path, $found['image_desktop']);
        }

        Storage::disk('media')->delete($path);
        Storage::disk('media')->deleteDirectory($dir);
    }

    public function test_company_work_schedule_public_payload_has_no_delivery_slots(): void
    {
        $this->skipUnlessTablesExist(['companies']);

        $company = SYS_Company::query()->first();
        if ($company === null) {
            $this->markTestSkipped('Нет записи companies для проверки контракта API.');
        }

        $originalSchedule = $company->work_schedule;

        try {
            $schedule = [
                [
                    'day' => 'mon',
                    'work' => '10:00-22:00',
                    'delivery' => '11:00-22:00',
                    'is_day_off' => '0',
                ],
            ];
            $company->work_schedule = $schedule;
            $company->save();

            $response = $this->getJson('/api/system/company');
            $response->assertOk();

            $data = $response->json('data');
            $this->assertIsArray($data);
            $this->assertArrayNotHasKey('delivery_hours', $data);

            $ws = $data['work_schedule'] ?? null;
            $this->assertIsArray($ws);
            $this->assertNotEmpty($ws);
            $first = $ws[0];
            $this->assertIsArray($first);
            $this->assertArrayNotHasKey('delivery', $first);
            $this->assertSame('10:00-22:00', $first['work'] ?? null);
        } finally {
            $company->work_schedule = $originalSchedule;
            $company->save();
        }
    }

    public function test_promotions_200_contract_with_real_file(): void
    {
        $dir = 'test-uploads/'.Str::uuid();
        $path = $dir.'/promo.png';
        Storage::disk('media')->put($path, $this->minimalPng());

        $promo = SYS_Promotion::query()->create([
            'title' => 'PHPUnit promo',
            'description' => 'Текст акции',
            'image' => $path,
        ]);
        $this->promotionId = $promo->id;

        $response = $this->getJson('/api/system/promotions');
        $response->assertOk();

        $list = $response->json('data');
        $this->assertIsArray($list);

        $found = null;
        foreach ($list as $row) {
            if ((int) ($row['id'] ?? 0) === (int) $promo->id) {
                $found = $row;
                break;
            }
        }

        $this->assertNotNull($found);
        $this->assertArrayHasKey('image', $found);
        $this->assertIsString($found['image']);

        Storage::disk('media')->delete($path);
        Storage::disk('media')->deleteDirectory($dir);
    }

    private function minimalPng(): string
    {
        // 1×1 PNG
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true) ?: '';
    }
}
