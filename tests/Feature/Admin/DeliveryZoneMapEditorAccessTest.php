<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DeliveryZoneMapEditorAccessTest extends TestCase
{
    public function test_guest_map_editor_redirects_to_login(): void
    {
        $this->skipUnlessUsersTableExists();

        $this->get(route('filament.admin.delivery-zone-map-editor'))
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_map_editor_returns_iframe_page(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('filament.admin.delivery-zone-map-editor'))
            ->assertOk()
            ->assertSee('api-maps.yandex.ru', false)
            ->assertSee('yandexGeoJsonCoords.js', false)
            ->assertSee('Нарисовать зону', false)
            ->assertSee('function bootstrapMap()', false)
            ->assertSee('Не задан YANDEX_MAPS_API_KEY', false);
    }

    public function test_map_editor_loads_ymaps_before_inline_bootstrap(): void
    {
        $this->skipUnlessUsersTableExists();

        Config::set('services.yandex_maps.api_key', 'test-maps-key');

        $user = User::factory()->create();

        $html = $this->actingAs($user)
            ->get(route('filament.admin.delivery-zone-map-editor'))
            ->assertOk()
            ->getContent();

        $ymapsPos = strpos($html, 'api-maps.yandex.ru');
        $bootstrapPos = strpos($html, 'function bootstrapMap()');

        $this->assertNotFalse($ymapsPos);
        $this->assertNotFalse($bootstrapPos);
        $this->assertLessThan($bootstrapPos, $ymapsPos);
    }

    public function test_operations_hub_includes_delivery_zone_bridge_asset(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/operations?tab=delivery')
            ->assertOk()
            ->assertSee('delivery-zone-iframe-bridge', false);
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
