<?php

namespace Tests\Feature\Api;

final class OrderApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipUnlessTablesExist([
            'UR_clients',
            'personal_access_tokens',
            'ORD_orders',
            'ORD_order_items',
            'PRD_products',
        ]);
    }

    public function test_index_401_without_token(): void
    {
        $this->getJson('/api/order')->assertUnauthorized();
    }

    public function test_index_200_returns_list_of_order_contracts(): void
    {
        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров — пропуск сценария заказов.');
        }

        $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        )->assertCreated();

        $response = $this->getJson('/api/order', $this->bearerSanctum($session['token']));
        $response->assertOk();
        $list = $response->json();
        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
        $this->assertOrderPresenterContract($list[0]);
    }

    public function test_store_401_without_token(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->postJson('/api/order', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
        ])->assertUnauthorized();
    }

    public function test_store_validation_422_empty_items(): void
    {
        $session = $this->registerClientViaApi();

        $this->postJson(
            '/api/order',
            [
                'items' => [],
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_store_validation_422_courier_without_address(): void
    {
        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'delivery_method' => 'courier',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_address']);
    }

    public function test_store_201_pickup_linked_client_contract(): void
    {
        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $response = $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 2]],
                'delivery_method' => 'pickup',
                'payment_method' => 'card',
            ],
            $this->bearerSanctum($session['token']),
        );

        $response->assertCreated();
        $this->assertOrderPresenterContract($response->json());
        $this->assertSame($session['client']['id'], $response->json('client_id'));
    }

    public function test_store_201_courier_uses_authenticated_client(): void
    {
        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $response = $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'delivery_method' => 'courier',
                'delivery_address' => [
                    'street' => 'Невский',
                    'house' => '1',
                    'entrance' => 'А',
                    'apartment' => '10',
                ],
                'delivery_comment' => 'Звонить',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        );

        $response->assertCreated();
        $this->assertOrderPresenterContract($response->json());
        $this->assertSame($session['client']['id'], $response->json('client_id'));
    }

    public function test_store_422_unknown_product(): void
    {
        $session = $this->registerClientViaApi();

        $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => 999999999, 'quantity' => 1]],
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        )
            ->assertStatus(422)
            ->assertJsonPath('message', 'Product not found: 999999999');
    }

    public function test_store_ignores_spoofed_client_id_and_uses_auth_client(): void
    {
        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $response = $this->postJson(
            '/api/order',
            [
                'client_id' => 999999999,
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        );

        $response->assertCreated();
        $this->assertSame($session['client']['id'], $response->json('client_id'));
    }
}
