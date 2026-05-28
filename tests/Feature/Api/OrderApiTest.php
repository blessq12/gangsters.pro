<?php

namespace Tests\Feature\Api;

use App\Mail\ClientOrderConfirmationMail;
use Illuminate\Support\Facades\Mail;

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
            'PRD_category_product',
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

    public function test_store_422_guest_without_contact(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->postJson('/api/order', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name', 'customer_phone']);
    }

    public function test_store_201_guest_pickup_without_client_id(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $guestPhone = '+79'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        $response = $this->postJson('/api/order', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
            'delivery_method' => 'pickup',
            'payment_method' => 'card',
            'customer_name' => 'Гость API',
            'customer_phone' => $guestPhone,
        ]);

        $response->assertCreated();
        $this->assertOrderPresenterContract($response->json());
        $this->assertNull($response->json('client_id'));
        $this->assertSame('Гость API', $response->json('customer.name'));
    }

    public function test_store_422_authenticated_prohibits_guest_contact_fields(): void
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
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
                'customer_name' => 'Spoof',
                'customer_phone' => '+79990000000',
            ],
            $this->bearerSanctum($session['token']),
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name', 'customer_phone']);
    }

    public function test_store_422_rejects_transfer_payment_method(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->postJson('/api/order', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
            'delivery_method' => 'pickup',
            'payment_method' => 'transfer',
            'customer_name' => 'Гость API',
            'customer_phone' => '+79991112233',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
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

    public function test_store_201_sends_order_confirmation_email(): void
    {
        Mail::fake();

        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $response = $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        );

        $response->assertCreated();
        $orderId = $response->json('id');

        Mail::assertSent(ClientOrderConfirmationMail::class, function (ClientOrderConfirmationMail $mail) use ($session, $orderId) {
            return $mail->hasTo($session['email'])
                && ($mail->order['id'] ?? null) === $orderId;
        });
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

    public function test_mark_paid_401_without_internal_token(): void
    {
        $this->postJson('/api/internal/orders/ORD-unknown/pay')
            ->assertUnauthorized();
    }

    public function test_mark_paid_200_sets_payment_status_paid(): void
    {
        $this->skipUnlessTablesExist([
            'UR_clients',
            'personal_access_tokens',
            'ORD_orders',
            'ORD_order_items',
            'PRD_products',
            'PRD_category_product',
            'reporting_client_order_facts',
        ]);

        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $created = $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        )->assertCreated();

        $orderId = (string) $created->json('id');

        $this->postJson(
            '/api/internal/orders/'.$orderId.'/pay',
            [],
            ['X-Internal-Api-Token' => (string) config('services.internal.api_token', '')],
        )
            ->assertOk()
            ->assertJsonPath('payment.status', 'paid');
    }
}
