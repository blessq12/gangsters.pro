<?php

namespace Tests\Feature\Api;

use App\Mail\ClientOrderConfirmationMail;
use Illuminate\Support\Facades\DB;
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
            'complimentary_item_rules',
            'complimentary_item_rule_categories',
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

    public function test_complimentary_preview_returns_free_items_for_trigger_category(): void
    {
        $this->markTestSkipped('Promotions vertical удалена: complimentary preview выведен из релизного API.');

        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->seedComplimentaryRuleForProduct($productId, $productId);

        $response = $this->postJson(
            '/api/order/complimentary-preview',
            [
                'items' => [['product_id' => $productId, 'quantity' => 2]],
            ],
        );

        $response->assertOk();
        $items = $response->json('items');
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $this->assertSame($productId, $items[0]['product_id']);
        $this->assertSame(1, $items[0]['quantity']);
    }

    public function test_store_adds_complimentary_item_once_per_order(): void
    {
        $this->markTestSkipped('Promotions vertical удалена: complimentary items больше не добавляются.');

        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->seedComplimentaryRuleForProduct($productId, $productId);

        $response = $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 2]],
                'delivery_method' => 'pickup',
                'payment_method' => 'cash',
            ],
            $this->bearerSanctum($session['token']),
        );

        $response->assertCreated();
        $items = $response->json('items');
        $this->assertIsArray($items);
        $this->assertCount(2, $items);

        $complimentaryItems = array_values(array_filter($items, static fn (array $row): bool => (bool) ($row['product']['attributes']['is_complimentary'] ?? false)));
        $this->assertCount(1, $complimentaryItems);
        $this->assertEqualsWithDelta(0.0, (float) $complimentaryItems[0]['product']['final_price'], 0.001);
        $this->assertSame(1, $complimentaryItems[0]['quantity']);
    }

    public function test_single_rule_with_multiple_categories_does_not_duplicate_complimentary_item(): void
    {
        $this->markTestSkipped('Promotions vertical удалена: правило complimentary не поддерживается.');

        DB::table('complimentary_item_rule_categories')->delete();
        DB::table('complimentary_item_rules')->delete();

        $pairs = DB::table('PRD_category_product')
            ->select(['category_id', 'product_id'])
            ->distinct()
            ->limit(20)
            ->get();

        $byCategory = [];
        foreach ($pairs as $row) {
            $categoryId = (int) $row->category_id;
            $productId = (int) $row->product_id;
            if (! isset($byCategory[$categoryId])) {
                $byCategory[$categoryId] = $productId;
            }
        }

        $categoryIds = array_keys($byCategory);
        if (count($categoryIds) < 2) {
            $this->markTestSkipped('Недостаточно категорий с товарами для проверки множественной логики.');
        }

        $firstCategoryId = (int) $categoryIds[0];
        $secondCategoryId = (int) $categoryIds[1];
        $firstProductId = (int) $byCategory[$firstCategoryId];
        $secondProductId = (int) $byCategory[$secondCategoryId];

        $this->seedComplimentaryRuleForCategories(
            [$firstCategoryId, $secondCategoryId],
            $firstProductId,
        );

        $response = $this->postJson(
            '/api/order/complimentary-preview',
            [
                'items' => [
                    ['product_id' => $firstProductId, 'quantity' => 1],
                    ['product_id' => $secondProductId, 'quantity' => 1],
                ],
            ],
        );

        $response->assertOk();
        $items = $response->json('items');
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
    }

    public function test_complimentary_preview_works_without_token(): void
    {
        $this->markTestSkipped('Promotions vertical удалена: complimentary preview выведен из релизного API.');

        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->seedComplimentaryRuleForProduct($productId, $productId);

        $response = $this->postJson('/api/order/complimentary-preview', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
        ]);

        $response->assertOk();
        $items = $response->json('items');
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
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

    private function seedComplimentaryRuleForProduct(int $triggerProductId, int $giftProductId): void
    {
        $categoryId = DB::table('PRD_category_product')
            ->where('product_id', $triggerProductId)
            ->value('category_id');

        if ($categoryId === null) {
            $this->markTestSkipped('Нет связи товара с категорией в PRD_category_product.');
        }

        $this->seedComplimentaryRuleForCategories([(int) $categoryId], $giftProductId);
    }

    /**
     * @param  array<int, int>  $categoryIds
     */
    private function seedComplimentaryRuleForCategories(array $categoryIds, int $giftProductId): void
    {
        $now = now();
        $ruleId = DB::table('complimentary_item_rules')->insertGetId([
            'trigger_category_id' => null,
            'gift_product_id' => $giftProductId,
            'is_active' => 1,
            'priority' => 100,
            'updated_at' => $now,
            'created_at' => $now,
        ]);

        foreach (array_values(array_unique($categoryIds)) as $categoryId) {
            DB::table('complimentary_item_rule_categories')->updateOrInsert([
                'rule_id' => (int) $ruleId,
                'category_id' => (int) $categoryId,
            ], [
                'updated_at' => $now,
                'created_at' => $now,
            ]);
        }
    }
}
