<?php

namespace Tests\Feature\Filament;

use App\Infrastructure\SystemContent\Model\SYS_Company;
use Tests\Feature\Api\ApiTestCase;

final class DeliveryZoneResourceTest extends ApiTestCase
{
    private const VALID_POLYGON = [
        'type' => 'Polygon',
        'coordinates' => [
            [
                [84.95, 56.48],
                [85.05, 56.48],
                [85.05, 56.52],
                [84.95, 56.52],
                [84.95, 56.48],
            ],
        ],
    ];

    /** @var array<string, mixed>|null */
    private ?array $originalGeojson = null;

    private ?float $originalLat = null;

    private ?float $originalLng = null;

    private ?int $companyId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->skipUnlessTablesExist(['companies']);
    }

    protected function tearDown(): void
    {
        if ($this->companyId !== null) {
            SYS_Company::query()->whereKey($this->companyId)->update([
                'delivery_zone_geojson' => $this->originalGeojson,
                'kitchen_latitude' => $this->originalLat,
                'kitchen_longitude' => $this->originalLng,
            ]);
        }

        parent::tearDown();
    }

    public function test_company_delivery_zone_geojson_exposed_in_api(): void
    {
        $company = SYS_Company::query()->first();
        if ($company === null) {
            $this->markTestSkipped('Нет записи companies для проверки.');
        }

        $this->companyId = (int) $company->id;
        $this->originalGeojson = $company->delivery_zone_geojson;
        $this->originalLat = $company->kitchen_latitude !== null ? (float) $company->kitchen_latitude : null;
        $this->originalLng = $company->kitchen_longitude !== null ? (float) $company->kitchen_longitude : null;

        $company->delivery_zone_geojson = self::VALID_POLYGON;
        $company->kitchen_latitude = 56.5;
        $company->kitchen_longitude = 85.0;
        $company->save();

        $response = $this->getJson('/api/system/company');
        $response->assertOk();

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertSame('Polygon', $data['delivery_zone_geojson']['type'] ?? null);
        $this->assertEqualsWithDelta(56.5, (float) ($data['kitchen_latitude'] ?? 0), 0.0001);
        $this->assertEqualsWithDelta(85.0, (float) ($data['kitchen_longitude'] ?? 0), 0.0001);
    }
}
