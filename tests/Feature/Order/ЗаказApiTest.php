<?php

namespace Tests\Feature\Order;

use Tests\ApiTestCase;

final class ЗаказApiTest extends ApiTestCase
{
    public function test_quote_считает_корзину_для_pickup(): void
    {
        $productId = $this->idАктивногоТовара();

        $ответ = $this->postJson('/api/order/quote', [
            'lines' => [
                ['product_id' => $productId, 'quantity' => 2],
            ],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'client' => [
                'kind' => 'guest',
                'name' => 'Гость',
                'phone' => '+7 (900) 111-22-33',
            ],
        ]);

        $ответ->assertOk()
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

        $this->assertSame('pickup', $ответ->json('data.delivery.method'));
        $this->assertGreaterThan(0, (int) $ответ->json('data.totals.items_rubles'));
        $this->assertCount(1, $ответ->json('data.cart.lines'));
        $this->assertSame(2, (int) $ответ->json('data.cart.lines.0.quantity'));
    }

    public function test_quote_отклоняет_пустую_корзину(): void
    {
        $ответ = $this->postJson('/api/order/quote', [
            'lines' => [],
            'delivery_method' => 'pickup',
        ]);

        $ответ->assertStatus(422);
    }

    public function test_place_создаёт_заказ_из_quote_снимка(): void
    {
        $productId = $this->idАктивногоТовара();
        $clientRequestId = 'phpunit-place-'.bin2hex(random_bytes(6));

        $quote = $this->postJson('/api/order/quote', [
            'lines' => [
                ['product_id' => $productId, 'quantity' => 1],
            ],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'client' => [
                'kind' => 'guest',
                'name' => 'Гость Заказ',
                'phone' => '+7 (900) 222-33-44',
            ],
        ])->json('data');

        $ответ = $this->postJson('/api/order/', [
            'client_request_id' => $clientRequestId,
            'cart' => $quote['cart'],
            'client' => $quote['client'],
            'delivery' => $quote['delivery'],
            'payment' => $quote['payment'],
        ]);

        $ответ->assertCreated()
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

        $this->assertGreaterThan(0, (int) $ответ->json('data.id'));
        $this->assertNotEmpty($ответ->json('data.items'));
    }

    public function test_place_идемпотентен_по_client_request_id(): void
    {
        $productId = $this->idАктивногоТовара();
        $clientRequestId = 'phpunit-idem-'.bin2hex(random_bytes(6));

        $quote = $this->postJson('/api/order/quote', [
            'lines' => [
                ['product_id' => $productId, 'quantity' => 1],
            ],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'client' => [
                'kind' => 'guest',
                'name' => 'Гость Идемпотентность',
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

        $первый = $this->postJson('/api/order/', $payload);
        $первый->assertCreated();

        $второй = $this->postJson('/api/order/', $payload);
        $второй->assertCreated();

        $this->assertSame(
            (int) $первый->json('data.id'),
            (int) $второй->json('data.id'),
        );
    }
}
