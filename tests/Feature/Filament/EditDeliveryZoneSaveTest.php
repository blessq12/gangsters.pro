<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\DeliveryZones\Pages\EditDeliveryZone;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class EditDeliveryZoneSaveTest extends TestCase
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
        if (! $this->databaseTableExists('companies') || ! $this->databaseTableExists('users')) {
            $this->markTestSkipped('Нет таблиц companies/users для Filament-теста.');
        }
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

    public function test_edit_delivery_zone_saves_polygon_via_filament_form(): void
    {
        $company = SYS_Company::query()->first();
        if ($company === null) {
            $this->markTestSkipped('Нет записи companies для проверки.');
        }

        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        $this->companyId = (int) $company->id;
        $this->originalGeojson = $company->delivery_zone_geojson;
        $this->originalLat = $company->kitchen_latitude !== null ? (float) $company->kitchen_latitude : null;
        $this->originalLng = $company->kitchen_longitude !== null ? (float) $company->kitchen_longitude : null;

        $company->update([
            'delivery_zone_geojson' => null,
            'kitchen_latitude' => null,
            'kitchen_longitude' => null,
        ]);

        Livewire::actingAs($user)
            ->test(EditDeliveryZone::class, ['record' => $company->getKey()])
            ->fillForm([
                'delivery_zone_geojson' => self::VALID_POLYGON,
                'kitchen_latitude' => 56.5,
                'kitchen_longitude' => 85.0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $company->refresh();

        $this->assertIsArray($company->delivery_zone_geojson);
        $this->assertSame('Polygon', $company->delivery_zone_geojson['type'] ?? null);
        $this->assertEqualsWithDelta(56.5, (float) $company->kitchen_latitude, 0.0001);
        $this->assertEqualsWithDelta(85.0, (float) $company->kitchen_longitude, 0.0001);
    }

    private function databaseTableExists(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
