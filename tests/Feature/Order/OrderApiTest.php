<?php

namespace Tests\Feature\Order;

use Tests\ApiTestCase;

final class OrderApiTest extends ApiTestCase
{
    public function test_quote_prices_cart_for_pickup(): void
    {
        $productId = $this->activeProductId();

        $response = $this->postJson('/api/order/quote', [
            'lines' => [
                ['product_id' => $productId, 'quantity' => 2],
            ],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'client' => [
                'kind' => 'guest',
                'name' => 'Guest',
                'phone' => '+7 (900) 111-22-33',
            ],
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'cart' => ['lines'],
                    'client',
                    'delivery' => ['method', 'delivery_fee_rubles'],
                    'payment',
                    'totals' => [
                        'items_rubles',
                        'delivery_fee_rubles',
                        'grand_total_rubles',
                    ],
                    'benefits',
                ],
            ]);

        $this->assertSame('pickup', $response->json('data.delivery.method'));
        $this->assertGreaterThan(0, (int) $response->json('data.totals.items_rubles'));
        $this->assertCount(1, $response->json('data.cart.lines'));
        $this->assertSame(2, (int) $response->json('data.cart.lines.0.quantity'));
    }

    public function test_quote_rejects_empty_cart(): void
    {
        $response = $this->postJson('/api/order/quote', [
            'lines' => [],
            'delivery_method' => 'pickup',
        ]);

        $response->assertStatus(422);
    }

    public function test_place_creates_order_from_quote_snapshot(): void
    {
        $productId = $this->activeProductId();
        $clientRequestId = 'phpunit-place-'.bin2hex(random_bytes(6));

        $quote = $this->postJson('/api/order/quote', [
            'lines' => [
                ['product_id' => $productId, 'quantity' => 1],
            ],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'client' => [
                'kind' => 'guest',
                'name' => 'Guest Order',
                'phone' => '+7 (900) 222-33-44',
            ],
        ])->json('data');

        $response = $this->postJson('/api/order/', [
            'client_request_id' => $clientRequestId,
            'cart' => $quote['cart'],
            'client' => $quote['client'],
            'delivery' => $quote['delivery'],
            'payment' => $quote['payment'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.client_request_id', $clientRequestId)
            ->assertJsonPath('data.status', 'new')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'source',
                    'client_request_id',
                    'status',
                    'total',
                    'client',
                    'delivery',
                    'payment',
                    'items',
                ],
            ]);

        $this->assertGreaterThan(0, (int) $response->json('data.id'));
        $this->assertNotEmpty($response->json('data.items'));
    }

    public function test_place_is_idempotent_by_client_request_id(): void
    {
        $productId = $this->activeProductId();
        $clientRequestId = 'phpunit-idem-'.bin2hex(random_bytes(6));

        $quote = $this->postJson('/api/order/quote', [
            'lines' => [
                ['product_id' => $productId, 'quantity' => 1],
            ],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'client' => [
                'kind' => 'guest',
                'name' => 'Guest Idempotency',
                'phone' => '+7 (900) 333-44-55',
            ],
        ])->json('data');

        $payload = [
            'client_request_id' => $clientRequestId,
            'cart' => $quote['cart'],
            'client' => $quote['client'],
            'delivery' => $quote['delivery'],
            'payment' => $quote['payment'],
        ];

        $first = $this->postJson('/api/order/', $payload);
        $first->assertCreated();

        $second = $this->postJson('/api/order/', $payload);
        $second->assertCreated();

        $this->assertSame(
            (int) $first->json('data.id'),
            (int) $second->json('data.id'),
        );
    }
}
