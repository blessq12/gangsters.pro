<?php

namespace Tests\Feature\Api;

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

        $this->withHeaders(['Cookie' => $cookieHeader])
            ->patchJson('/api/shopping/checkout-draft', [
                'promotions' => [
                    'free_roll_gift_product_id' => 42,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.checkout_draft.promotions.free_roll_gift_product_id', 42);
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
}
