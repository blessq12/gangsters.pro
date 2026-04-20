<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;

final class ShoppingApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
            ],
        ]);
        $response->assertCookie((string) config('shopping.session_cookie'));
        $this->assertNotEmpty($response->json('data.session.public_id'));
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
        $state = $this->getJson('/api/shopping/state')->assertOk();
        $cookieHeader = $this->cookieHeaderFromResponse($state);

        $giftProductId = $this->firstActiveProductId();
        if ($giftProductId === null) {
            $this->markTestSkipped('Нет активных товаров для проверки подарка.');
        }

        DB::table('PRD_products')
            ->where('id', $giftProductId)
            ->update(['cart_rule_gift_candidate' => true]);

        $this->withHeaders(['Cookie' => $cookieHeader])
            ->patchJson('/api/shopping/checkout-draft', [
                'promotions' => [
                    'free_roll_gift_product_id' => $giftProductId,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.checkout_draft.promotions.free_roll_gift_product_id', $giftProductId);
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
