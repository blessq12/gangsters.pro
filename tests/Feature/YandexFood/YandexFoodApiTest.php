<?php

namespace Tests\Feature\YandexFood;

use Tests\ApiTestCase;

final class YandexFoodApiTest extends ApiTestCase
{
    /**
     * @var list<string>
     */
    protected array $requiredTables = [
        'PRD_products',
        'PRD_categories',
        'CMP_company',
        'DLV_configuration',
    ];

    private const BEARER = 'phpunit-yandex-bearer-token';

    private const CLIENT_ID = 'phpunit-yandex-client-id';

    private const CLIENT_SECRET = 'phpunit-yandex-client-secret';

    public function test_oauth_issues_access_token(): void
    {
        $response = $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
        ]);

        $response->assertOk()
            ->assertJsonPath('access_token', self::BEARER);
    }

    public function test_oauth_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => 'wrong-id',
            'client_secret' => 'wrong-secret',
        ]);

        // Exception is not mapped to 4xx yet — assert current contract.
        $response->assertStatus(500);
    }

    public function test_restaurants_returns_places_with_bearer(): void
    {
        $response = $this->withBearer(self::BEARER)
            ->getJson('/api/yandex-food/restaurants');

        $response->assertOk()
            ->assertJsonStructure([
                'places' => [
                    ['id', 'title', 'address'],
                ],
            ]);

        $this->assertCount(1, $response->json('places'));
        $this->assertNotEmpty($response->json('places.0.title'));
    }

    public function test_menu_composition_returns_catalog(): void
    {
        $response = $this->withBearer(self::BEARER)
            ->getJson('/api/yandex-food/menu/1/composition');

        $response->assertOk()
            ->assertJsonStructure([
                'categories',
                'items',
                'lastChange',
            ]);

        $this->assertNotEmpty($response->json('categories'));
        $this->assertNotEmpty($response->json('items'));
        $this->assertIsString($response->json('lastChange'));
    }

    public function test_menu_availability_returns_items_and_modifiers(): void
    {
        $response = $this->withBearer(self::BEARER)
            ->getJson('/api/yandex-food/menu/1/availability');

        $response->assertOk()
            ->assertJsonStructure([
                'items',
                'modifiers',
            ]);

        $this->assertIsArray($response->json('items'));
        $this->assertIsArray($response->json('modifiers'));
    }

    public function test_menu_promos_returns_promo_items(): void
    {
        $response = $this->withBearer(self::BEARER)
            ->getJson('/api/yandex-food/menu/1/promos');

        $response->assertOk()
            ->assertJsonPath('promoItems', []);
    }

    public function test_protected_endpoints_without_bearer_return_400(): void
    {
        $this->getJson('/api/yandex-food/restaurants')
            ->assertStatus(400)
            ->assertJsonStructure(['reason']);

        $this->getJson('/api/yandex-food/menu/1/composition')
            ->assertStatus(400);

        $this->withToken('invalid-token')
            ->getJson('/api/yandex-food/restaurants')
            ->assertStatus(400)
            ->assertJsonStructure(['reason']);
    }
}
