<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Str;

final class YandexFoodApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipUnlessTablesExist([
            'PRD_categories',
            'PRD_category_product',
            'PRD_products',
            'ORD_orders',
            'reporting_client_order_facts',
        ]);

        if (! (bool) config('services.yandex_food.enabled', true)) {
            $this->markTestSkipped('Yandex Food integration is disabled by config.');
        }
    }

    private function yandexAuthHeader(): array
    {
        $token = (string) config('services.yandex_food.auth_token', '');

        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_login_400_without_credentials(): void
    {
        $this->postJson('/api/yandex-food/security/oauth/token', [])
            ->assertStatus(400)
            ->assertJsonPath('code', 100);
    }

    public function test_login_400_invalid_client(): void
    {
        $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => 'wrong',
            'client_secret' => 'wrong',
        ])
            ->assertStatus(400)
            ->assertJsonPath('code', 100);
    }

    public function test_login_200_returns_access_token(): void
    {
        $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => (string) config('services.yandex_food.client_id', ''),
            'client_secret' => (string) config('services.yandex_food.client_secret', ''),
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token']);
    }

    public function test_menu_routes_400_without_bearer(): void
    {
        $this->getJson('/api/yandex-food/menu/1/composition')->assertStatus(400);
        $this->getJson('/api/yandex-food/menu/1/availability')->assertStatus(400);
        $this->getJson('/api/yandex-food/menu/1/promos')->assertStatus(400);
    }

    public function test_menu_composition_200_contract(): void
    {
        $this->getJson('/api/yandex-food/menu/1/composition', $this->yandexAuthHeader())
            ->assertOk()
            ->assertJsonStructure([
                'categories',
                'items',
                'lastChange',
            ]);
    }

    public function test_menu_availability_200_contract(): void
    {
        $this->getJson('/api/yandex-food/menu/1/availability', $this->yandexAuthHeader())
            ->assertOk()
            ->assertJsonStructure([
                'items',
                'modifiers',
            ]);
    }

    public function test_menu_promos_200_contract(): void
    {
        $this->getJson('/api/yandex-food/menu/1/promos', $this->yandexAuthHeader())
            ->assertOk()
            ->assertJsonPath('promoItems', []);
    }

    public function test_create_order_400_on_invalid_payload(): void
    {
        $this->postJson('/api/yandex-food/order', ['foo' => 1], $this->yandexAuthHeader())
            ->assertStatus(400)
            ->assertJsonPath('code', 100);
    }

    public function test_create_order_200_when_catalog_has_product(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $payload = [
            'discriminator' => 'courier',
            'eatsId' => (string) Str::uuid(),
            'restaurantId' => '1',
            'deliveryInfo' => [
                'clientName' => 'Тест Клиент',
                'phoneNumber' => '+79991234567',
                'deliveryDate' => now()->addDay()->toIso8601String(),
                'deliveryAddress' => [
                    'full' => 'СПб, тест, 1',
                    'latitude' => 59.93,
                    'longitude' => 30.33,
                    'street' => 'Тестовая',
                    'house' => '1',
                ],
            ],
            'paymentInfo' => [
                'paymentType' => 'cash',
                'itemsCost' => 100,
                'deliveryFee' => 0,
                'total' => 100,
                'change' => 0,
            ],
            'items' => [
                [
                    'id' => (string) $productId,
                    'quantity' => 1,
                    'price' => 100,
                ],
            ],
            'persons' => 1,
            'comment' => '',
            'promos' => [],
        ];

        $response = $this->postJson('/api/yandex-food/order', $payload, $this->yandexAuthHeader());

        $response->assertOk();
        $response->assertJsonPath('result', 'OK');
        $this->assertArrayHasKey('orderId', $response->json());
    }

    public function test_get_order_200_after_create(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $payload = [
            'discriminator' => 'pickup',
            'eatsId' => (string) Str::uuid(),
            'restaurantId' => '1',
            'deliveryInfo' => [
                'clientName' => 'Тест',
                'phoneNumber' => '+79997654321',
                'deliveryDate' => now()->addDay()->toIso8601String(),
                'deliveryAddress' => [
                    'full' => 'Самовывоз',
                    'latitude' => 0.0,
                    'longitude' => 0.0,
                ],
            ],
            'paymentInfo' => [
                'paymentType' => 'cash',
                'itemsCost' => 50,
                'deliveryFee' => 0,
                'total' => 50,
                'change' => 0,
            ],
            'items' => [
                [
                    'id' => (string) $productId,
                    'quantity' => 1,
                    'price' => 50,
                ],
            ],
            'persons' => 1,
            'comment' => '',
            'promos' => [],
        ];

        $create = $this->postJson('/api/yandex-food/order', $payload, $this->yandexAuthHeader());
        $create->assertOk();
        $orderId = $create->json('orderId');
        $this->assertNotEmpty($orderId);

        $get = $this->getJson('/api/yandex-food/order/'.$orderId, $this->yandexAuthHeader());
        $get->assertOk();
        $get->assertJsonPath('result', 'OK');
        $get->assertJsonStructure(['result', 'order']);
    }

    public function test_get_order_400_for_unknown_id(): void
    {
        $fakeId = (string) Str::uuid();
        $this->getJson('/api/yandex-food/order/'.$fakeId, $this->yandexAuthHeader())
            ->assertStatus(400)
            ->assertJsonPath('code', 100);
    }

    public function test_order_status_400_for_unknown_id(): void
    {
        $fakeId = (string) Str::uuid();
        $this->getJson('/api/yandex-food/order/'.$fakeId.'/status', $this->yandexAuthHeader())
            ->assertStatus(400)
            ->assertJsonPath('code', 100);
    }

    public function test_update_order_400_invalid_body(): void
    {
        $fakeId = (string) Str::uuid();
        $this->putJson('/api/yandex-food/order/'.$fakeId.'/', ['x' => 1], $this->yandexAuthHeader())
            ->assertStatus(400)
            ->assertJsonPath('code', 100);
    }

    public function test_delete_order_400_without_eats_id_match(): void
    {
        $fakeId = (string) Str::uuid();
        $this->deleteJson(
            '/api/yandex-food/order/'.$fakeId.'/',
            [],
            $this->yandexAuthHeader(),
        )
            ->assertStatus(400)
            ->assertJsonPath('code', 100);
    }

    public function test_restaurants_skipped_by_policy(): void
    {
        $this->markTestSkipped('Рестораны: выборка из Company без контрактного теста (данные из БД).');
    }
}
