<?php

namespace Tests\Feature\YandexFood;

use Tests\ApiTestCase;

final class ЯндексЕдаApiTest extends ApiTestCase
{
    /**
     * @var list<string>
     */
    protected array $обязательныеТаблицы = [
        'PRD_products',
        'PRD_categories',
        'CMP_company',
        'DLV_configuration',
    ];

    private const BEARER = 'phpunit-yandex-bearer-token';

    private const CLIENT_ID = 'phpunit-yandex-client-id';

    private const CLIENT_SECRET = 'phpunit-yandex-client-secret';

    public function test_oauth_выдаёт_access_token(): void
    {
        $ответ = $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
        ]);

        $ответ->assertOk()
            ->assertJsonPath('access_token', self::BEARER);
    }

    public function test_oauth_отклоняет_невалидные_credentials(): void
    {
        $ответ = $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => 'wrong-id',
            'client_secret' => 'wrong-secret',
        ]);

        // Сейчас исключение не мапится в 4xx — контракт как есть.
        $ответ->assertStatus(500);
    }

    public function test_restaurants_отдаёт_places_с_bearer(): void
    {
        $ответ = $this->сТокеном(self::BEARER)
            ->getJson('/api/yandex-food/restaurants');

        $ответ->assertOk()
            ->assertJsonStructure([
                'places' => [
                    ['id', 'title', 'address'],
                ],
            ]);

        $this->assertCount(1, $ответ->json('places'));
        $this->assertNotEmpty($ответ->json('places.0.title'));
    }

    public function test_menu_composition_отдаёт_каталог(): void
    {
        $ответ = $this->сТокеном(self::BEARER)
            ->getJson('/api/yandex-food/menu/1/composition');

        $ответ->assertOk()
            ->assertJsonStructure([
                'categories',
                'items',
                'lastChange',
            ]);

        $this->assertNotEmpty($ответ->json('categories'));
        $this->assertNotEmpty($ответ->json('items'));
        $this->assertIsString($ответ->json('lastChange'));
    }

    public function test_menu_availability_отдаёт_items_и_modifiers(): void
    {
        $ответ = $this->сТокеном(self::BEARER)
            ->getJson('/api/yandex-food/menu/1/availability');

        $ответ->assertOk()
            ->assertJsonStructure([
                'items',
                'modifiers',
            ]);

        $this->assertIsArray($ответ->json('items'));
        $this->assertIsArray($ответ->json('modifiers'));
    }

    public function test_menu_promos_отдаёт_promoItems(): void
    {
        $ответ = $this->сТокеном(self::BEARER)
            ->getJson('/api/yandex-food/menu/1/promos');

        $ответ->assertOk()
            ->assertJsonPath('promoItems', []);
    }

    public function test_защищённые_эндпоинты_без_bearer_дают_400(): void
    {
        $this->getJson('/api/yandex-food/restaurants')
            ->assertStatus(400)
            ->assertJsonStructure(['reason']);

        $this->getJson('/api/yandex-food/menu/1/composition')
            ->assertStatus(400);

        $this->withToken('неверный-токен')
            ->getJson('/api/yandex-food/restaurants')
            ->assertStatus(400)
            ->assertJsonStructure(['reason']);
    }
}
