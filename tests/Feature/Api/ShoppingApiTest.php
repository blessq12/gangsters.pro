<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;

final class ShoppingApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->disableCookieEncryption();
        $this->withCredentials();
        $this->skipUnlessTablesExist([
            'SHP_shopping_sessions',
            'SHP_shopping_cart_lines',
            'SHP_shopping_favorites',
            'SHP_shopping_checkout_drafts',
            'SHP_shopping_cart_rule_settings',
            'PRD_products',
            'PRD_category_product',
            'PRD_categories',
            'UR_clients',
            'personal_access_tokens',
        ]);
    }

    public function test_state_returns_data_and_sets_cookie(): void
    {
        $response = $this->getJson('/api/shopping/state');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'session',
                'cart' => [
                    'items',
                    'subtotal_rub',
                    'subtotal_kopecks',
                    'promo_state',
                    'subtotal_user_kopecks',
                    'subtotal_system_kopecks',
                ],
                'favorites',
                'checkout_draft',
                'checkout_intent',
                'suggested_step',
            ],
        ]);
        $response->assertCookie((string) config('shopping.session_cookie'));
        $this->assertNotEmpty($response->json('data.session.public_id'));
        $response->assertJsonPath('data.suggested_step', 'cart');
        $this->assertSame(
            $response->json('data.checkout_draft'),
            $response->json('data.checkout_intent'),
        );
    }

    public function test_suggested_step_cart_when_cart_has_items_and_guest_without_draft_progress(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $state = $this->getJson('/api/shopping/state')->assertOk();
        $cookieHeader = $this->cookieHeaderFromResponse($state);

        $response = $this->withHeaders(['Cookie' => $cookieHeader])
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $response->assertJsonPath('data.suggested_step', 'cart');
    }

    public function test_suggested_step_payment_when_delivery_draft_complete(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $cookieName = (string) config('shopping.session_cookie');
        $cart = $this->postJson('/api/shopping/cart/items', [
            'product_id' => $productId,
            'quantity' => 1,
        ])->assertOk();
        $sessionPublicId = $cart->json('data.session.public_id');

        $response = $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'guest_contact' => [
                    'phone' => '+79991234567',
                ],
                'delivery_info' => [
                    'method' => 'courier',
                    'address' => [
                        'street' => 'Ленина',
                        'house' => '1',
                    ],
                ],
            ])
            ->assertOk();

        $response->assertJsonPath('data.session.public_id', $sessionPublicId);
        $response->assertJsonPath('data.suggested_step', 'guest');
    }

    public function test_suggested_step_delivery_when_guest_contact_complete_but_courier_address_missing(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $cookieName = (string) config('shopping.session_cookie');
        $cart = $this->postJson('/api/shopping/cart/items', [
            'product_id' => $productId,
            'quantity' => 1,
        ])->assertOk();
        $sessionPublicId = $cart->json('data.session.public_id');

        $response = $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'guest_contact' => [
                    'name' => 'Иван',
                    'phone' => '+79991234567',
                ],
                'delivery_info' => [
                    'method' => 'courier',
                ],
            ])
            ->assertOk();

        $response->assertJsonPath('data.suggested_step', 'delivery');
    }

    public function test_suggested_step_payment_when_guest_contact_and_delivery_complete(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $cookieName = (string) config('shopping.session_cookie');
        $cart = $this->postJson('/api/shopping/cart/items', [
            'product_id' => $productId,
            'quantity' => 1,
        ])->assertOk();
        $sessionPublicId = $cart->json('data.session.public_id');

        $response = $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'guest_contact' => [
                    'name' => 'Иван',
                    'phone' => '+79991234567',
                ],
                'delivery_info' => [
                    'method' => 'courier',
                    'address' => [
                        'street' => 'Ленина',
                        'house' => '1',
                    ],
                ],
            ])
            ->assertOk();

        $response->assertJsonPath('data.suggested_step', 'payment');
    }

    public function test_suggested_step_confirm_when_draft_has_payment(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $cookieName = (string) config('shopping.session_cookie');
        $cart = $this->postJson('/api/shopping/cart/items', [
            'product_id' => $productId,
            'quantity' => 1,
        ])->assertOk();
        $sessionPublicId = $cart->json('data.session.public_id');

        $response = $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'guest_contact' => [
                    'phone' => '+79991234567',
                ],
                'delivery_info' => [
                    'method' => 'pickup',
                ],
                'payment_info' => [
                    'method' => 'card',
                ],
            ])
            ->assertOk();

        $response->assertJsonPath('data.suggested_step', 'guest');
    }

    public function test_suggested_step_cart_after_order_and_promotions_patch_does_not_resume(): void
    {
        $this->skipUnlessTablesExist([
            'ORD_orders',
            'ORD_order_items',
        ]);

        $productId = $this->firstProductIdFromCatalog();
        $giftProductId = $this->firstActiveProductId();
        if ($productId === null || $giftProductId === null) {
            $this->markTestSkipped('Нет активных товаров для сценария заказа и подарка.');
        }

        DB::table('PRD_products')
            ->where('id', $giftProductId)
            ->update(['cart_rule_gift_candidate' => true]);

        DB::table('SHP_shopping_cart_rule_settings')->updateOrInsert(
            ['id' => 1],
            [
                'complement_rule_enabled' => true,
                'gift_rule_enabled' => true,
                'rolls_per_complement' => 2,
                'complement_rule_sort' => 10,
                'gift_rule_sort' => 20,
                'gift_threshold_kopecks' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $cookieName = (string) config('shopping.session_cookie');
        $state = $this->getJson('/api/shopping/state')->assertOk();
        $sessionPublicId = $state->json('data.session.public_id');
        $guestPhone = '+79'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        $this->withCookie($cookieName, $sessionPublicId)
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'guest_contact' => [
                    'name' => 'Гость после заказа',
                    'phone' => $guestPhone,
                ],
                'delivery_info' => [
                    'method' => 'pickup',
                ],
                'payment_info' => [
                    'method' => 'card',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.suggested_step', 'confirm');

        $this->withCookie($cookieName, $sessionPublicId)
            ->postJson('/api/order', [
                'items' => [],
                'delivery_method' => 'pickup',
                'payment_method' => 'card',
                'customer_name' => 'Гость после заказа',
                'customer_phone' => $guestPhone,
            ])
            ->assertCreated();

        $afterOrder = $this->withCookie($cookieName, $sessionPublicId)
            ->getJson('/api/shopping/state')
            ->assertOk();

        $afterOrder->assertJsonPath('data.suggested_step', 'cart');
        $this->assertNull($afterOrder->json('data.checkout_draft'));

        $newCart = $this->withCookie($cookieName, $sessionPublicId)
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $newCart->assertJsonPath('data.suggested_step', 'cart');
        $this->assertNull($newCart->json('data.checkout_draft'));

        $giftPatch = $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'promotions' => [
                    'free_roll_gift_product_id' => $giftProductId,
                ],
            ])
            ->assertOk();

        $giftPatch->assertJsonPath('data.suggested_step', 'cart');
        $giftPatch->assertJsonPath(
            'data.checkout_draft.promotions.free_roll_gift_product_id',
            $giftProductId,
        );
        $this->assertNull($giftPatch->json('data.checkout_draft.delivery_info'));
        $this->assertNull($giftPatch->json('data.checkout_draft.payment_info'));
    }

    public function test_upsert_cart_line_then_recalculate(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $jar = $this->getJson('/api/shopping/state')->assertOk();
        $cookies = $jar->headers->getCookies();
        $cookieHeader = '';
        foreach ($cookies as $c) {
            $cookieHeader .= $c->getName().'='.$c->getValue().'; ';
        }

        $upsert = $this->withHeaders(['Cookie' => trim($cookieHeader)])
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $productId,
                'quantity' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('data.cart.items.0.qty', 2);

        $upsert->assertJsonStructure([
            'data' => [
                'cart' => [
                    'items' => [
                        [
                            'productId',
                            'qty',
                            'line_key',
                            'origin',
                            'productSnapshot',
                            'pricing' => [
                                'list_unit_price_kopecks',
                                'final_unit_price_kopecks',
                                'line_total_kopecks',
                            ],
                        ],
                    ],
                    'subtotal_rub',
                    'subtotal_kopecks',
                    'subtotal_user_kopecks',
                    'subtotal_system_kopecks',
                    'promo_state',
                ],
            ],
        ]);

        $this->withHeaders(['Cookie' => trim($cookieHeader)])
            ->postJson('/api/shopping/cart/recalculate')
            ->assertOk();
    }

    public function test_checkout_draft_accepts_promotions_free_roll_gift_product_id(): void
    {
        $cartProductId = $this->firstProductIdFromCatalog();
        $giftProductId = $this->firstActiveProductId();
        if ($cartProductId === null || $giftProductId === null) {
            $this->markTestSkipped('Нет активных товаров для проверки подарка.');
        }

        DB::table('PRD_products')
            ->where('id', $giftProductId)
            ->update(['cart_rule_gift_candidate' => true]);

        DB::table('SHP_shopping_cart_rule_settings')->updateOrInsert(
            ['id' => 1],
            [
                'complement_rule_enabled' => true,
                'gift_rule_enabled' => true,
                'rolls_per_complement' => 2,
                'complement_rule_sort' => 10,
                'gift_rule_sort' => 20,
                'gift_threshold_kopecks' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $cookieName = (string) config('shopping.session_cookie');
        $state = $this->getJson('/api/shopping/state')->assertOk();
        $sessionPublicId = $state->json('data.session.public_id');

        $this->withCookie($cookieName, $sessionPublicId)
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $cartProductId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'promotions' => [
                    'free_roll_gift_product_id' => $giftProductId,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.checkout_draft.promotions.free_roll_gift_product_id', $giftProductId)
            ->assertJsonPath('data.cart.promo_state.gift_promotion.phase', 'gift_applied');
    }

    public function test_checkout_draft_rejects_transfer_payment_method(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $cookieName = (string) config('shopping.session_cookie');
        $cart = $this->postJson('/api/shopping/cart/items', [
            'product_id' => $productId,
            'quantity' => 1,
        ])->assertOk();
        $sessionPublicId = $cart->json('data.session.public_id');

        $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'payment_info' => [
                    'method' => 'transfer',
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payment_info.method']);
    }

    public function test_checkout_draft_rejects_non_gift_candidate_product_id(): void
    {
        $state = $this->getJson('/api/shopping/state')->assertOk();
        $cookieHeader = $this->cookieHeaderFromResponse($state);

        $notGiftProductId = $this->firstActiveProductId();
        if ($notGiftProductId === null) {
            $this->markTestSkipped('Нет активных товаров для проверки валидации подарка.');
        }

        DB::table('PRD_products')
            ->where('id', $notGiftProductId)
            ->update(['cart_rule_gift_candidate' => false]);

        $this->withHeaders(['Cookie' => $cookieHeader])
            ->patchJson('/api/shopping/checkout-draft', [
                'promotions' => [
                    'free_roll_gift_product_id' => $notGiftProductId,
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['promotions.free_roll_gift_product_id']);
    }

    public function test_gift_promotion_contains_candidate_items_for_modal(): void
    {
        $giftProductId = $this->firstActiveProductId();
        if ($giftProductId === null) {
            $this->markTestSkipped('Нет активных товаров для проверки gift promo.');
        }

        DB::table('PRD_products')
            ->where('id', $giftProductId)
            ->update([
                'cart_rule_gift_candidate' => true,
                'status' => 'active',
            ]);

        DB::table('SHP_shopping_cart_rule_settings')->updateOrInsert(
            ['id' => 1],
            [
                'complement_rule_enabled' => true,
                'gift_rule_enabled' => true,
                'rolls_per_complement' => 2,
                'complement_rule_sort' => 10,
                'gift_rule_sort' => 20,
                'gift_threshold_kopecks' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $state = $this->getJson('/api/shopping/state')->assertOk();
        $cookieHeader = $this->cookieHeaderFromResponse($state);

        $response = $this->withHeaders(['Cookie' => $cookieHeader])
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $giftProductId,
                'quantity' => 1,
            ])
            ->assertOk();

        $response->assertJsonPath('data.cart.promo_state.gift_promotion.eligible', true);
        $response->assertJsonPath('data.cart.promo_state.gift_promotion.selected_product_id', null);
        $response->assertJsonPath('data.cart.promo_state.gift_promotion.phase', 'select_gift');
        $response->assertJsonPath('data.cart.promo_state.gift_promotion.candidate_items.0.id', $giftProductId);
        $response->assertJsonStructure([
            'data' => [
                'cart' => [
                    'promo_state' => [
                        'gift_promotion' => [
                            'candidate_items' => [
                                [
                                    'id',
                                    'name',
                                    'price_kopecks',
                                    'price_rub',
                                    'image_url',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_gift_removed_from_cart_and_draft_when_user_subtotal_below_threshold(): void
    {
        $cartProductId = $this->firstProductIdFromCatalog();
        $giftProductId = $this->firstActiveProductId();
        if ($cartProductId === null || $giftProductId === null) {
            $this->markTestSkipped('Нет активных товаров для проверки gift threshold.');
        }

        DB::table('PRD_products')
            ->where('id', $giftProductId)
            ->update([
                'cart_rule_gift_candidate' => true,
                'status' => 'active',
            ]);

        DB::table('SHP_shopping_cart_rule_settings')->updateOrInsert(
            ['id' => 1],
            [
                'complement_rule_enabled' => true,
                'gift_rule_enabled' => true,
                'rolls_per_complement' => 2,
                'complement_rule_sort' => 10,
                'gift_rule_sort' => 20,
                'gift_threshold_kopecks' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $cookieName = (string) config('shopping.session_cookie');
        $state = $this->getJson('/api/shopping/state')->assertOk();
        $sessionPublicId = $state->json('data.session.public_id');

        $this->withCookie($cookieName, $sessionPublicId)
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $cartProductId,
                'quantity' => 1,
            ])
            ->assertOk();

        $this->withCookie($cookieName, $sessionPublicId)
            ->patchJson('/api/shopping/checkout-draft', [
                'promotions' => [
                    'free_roll_gift_product_id' => $giftProductId,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.cart.promo_state.gift_promotion.phase', 'gift_applied');

        $response = $this->withCookie($cookieName, $sessionPublicId)
            ->deleteJson('/api/shopping/cart/items/'.$cartProductId)
            ->assertOk();

        $response->assertJsonMissingPath('data.cart.promo_state.gift_promotion');
        $response->assertJsonPath('data.checkout_draft.promotions.free_roll_gift_product_id', null);

        $items = $response->json('data.cart.items') ?? [];
        foreach ($items as $item) {
            $this->assertNotSame('gift:free_roll', $item['line_key'] ?? null);
        }

        $this->withCookie($cookieName, $sessionPublicId)
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $cartProductId,
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.cart.promo_state.gift_promotion.phase', 'select_gift')
            ->assertJsonPath('data.cart.promo_state.gift_promotion.selected_product_id', null);
    }

    public function test_merge_attaches_client_id(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $state = $this->getJson('/api/shopping/state')->assertOk();
        $cookieHeader = $this->cookieHeaderFromResponse($state);

        $this->withHeaders(['Cookie' => $cookieHeader])
            ->postJson('/api/shopping/cart/items', [
                'product_id' => $productId,
                'quantity' => 1,
            ])
            ->assertOk();

        $session = $this->registerClientViaApi();

        $merge = $this->withHeaders(array_merge(
            ['Cookie' => $cookieHeader],
            $this->bearerSanctum($session['token']),
        ))->postJson('/api/shopping/merge')->assertOk();

        $clientId = (int) ($session['client']['id'] ?? 0);
        $this->assertGreaterThan(0, $clientId);
        $merge->assertJsonPath('data.session.client_id', $clientId);
    }

    private function cookieHeaderFromResponse(\Illuminate\Testing\TestResponse $response): string
    {
        $parts = [];
        foreach ($response->headers->getCookies() as $c) {
            $parts[] = $c->getName().'='.$c->getValue();
        }

        return implode('; ', $parts);
    }

    private function shoppingSessionCookieHeader(\Illuminate\Testing\TestResponse $response): string
    {
        $name = (string) config('shopping.session_cookie');

        return $name.'='.$this->shoppingSessionCookieValue($response);
    }

    private function shoppingSessionCookieValue(\Illuminate\Testing\TestResponse $response): string
    {
        $name = (string) config('shopping.session_cookie');
        foreach ($response->headers->getCookies() as $c) {
            if ($c->getName() === $name) {
                return $c->getValue();
            }
        }

        $this->fail("Shopping session cookie [{$name}] not found in response.");
    }

    private function firstActiveProductId(): ?int
    {
        $id = DB::table('PRD_products')
            ->where('status', 'active')
            ->where('price', '>', 0)
            ->orderBy('id')
            ->value('id');

        return is_numeric($id) ? (int) $id : null;
    }
}
