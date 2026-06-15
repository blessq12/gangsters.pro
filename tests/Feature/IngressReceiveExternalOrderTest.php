<?php

namespace Tests\Feature;

use App\Infrastructure\AggregatorIngress\Model\ING_PartnerSkuBinding;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IngressReceiveExternalOrderTest extends TestCase
{
    #[Test]
    public function ingress_stub_создаёт_заказ(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $productId = $this->resolveFirstProductId();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров.');
        }

        ING_PartnerSkuBinding::query()->updateOrCreate(
            [
                'partner_code' => 'stub',
                'partner_sku' => 'STUB-SKU-1',
            ],
            [
                'product_id' => $productId,
            ],
        );

        $payload = [
            'external_order_id' => 'stub-order-'.uniqid(),
            'placed_at' => '2026-06-15T12:00:00+00:00',
            'client' => [
                'name' => 'Агрегатор Клиент',
                'phone' => '+79991112233',
            ],
            'delivery' => [
                'method' => 'pickup',
            ],
            'payment' => [
                'method' => 'card_online',
            ],
            'lines' => [
                [
                    'partner_sku' => 'STUB-SKU-1',
                    'quantity' => 2,
                    'unit_price_rubles' => 500,
                ],
            ],
        ];

        $response = $this->postJson('/api/ingress/stub/orders', $payload, [
            'X-Ingress-Api-Key' => 'stub-dev-key',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'accepted');
        $response->assertJsonStructure([
            'order_id',
            'order' => [
                'id',
                'source',
                'partner_code',
                'external_order_id',
                'status',
            ],
        ]);
        $response->assertJsonPath('order.source', 'aggregator');
        $response->assertJsonPath('order.partner_code', 'stub');
        $response->assertJsonPath('order.external_order_id', $payload['external_order_id']);
        $response->assertJsonPath('order.checkout_id', null);
    }

    #[Test]
    public function ingress_stub_идемпотентен_по_external_order_id(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $productId = $this->resolveFirstProductId();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров.');
        }

        ING_PartnerSkuBinding::query()->updateOrCreate(
            [
                'partner_code' => 'stub',
                'partner_sku' => 'STUB-SKU-1',
            ],
            [
                'product_id' => $productId,
            ],
        );

        $externalOrderId = 'stub-idempotent-'.uniqid();
        $payload = [
            'external_order_id' => $externalOrderId,
            'placed_at' => '2026-06-15T12:00:00+00:00',
            'client' => [
                'name' => 'Повтор',
                'phone' => '+79990000001',
            ],
            'delivery' => [
                'method' => 'pickup',
            ],
            'payment' => [
                'method' => 'cash',
            ],
            'lines' => [
                [
                    'partner_sku' => 'STUB-SKU-1',
                    'quantity' => 1,
                    'unit_price_rubles' => 300,
                ],
            ],
        ];

        $headers = ['X-Ingress-Api-Key' => 'stub-dev-key'];

        $first = $this->postJson('/api/ingress/stub/orders', $payload, $headers);
        $first->assertOk();
        $orderId = $first->json('order_id');

        $second = $this->postJson('/api/ingress/stub/orders', $payload, $headers);
        $second->assertOk();
        $second->assertJsonPath('order_id', $orderId);
    }

    private function resolveFirstProductId(): ?int
    {
        $catalogResponse = $this->getJson('/api/catalog');
        $catalogResponse->assertOk();

        foreach ($catalogResponse->json('categories', []) as $categoryNode) {
            foreach ($categoryNode['items'] ?? [] as $item) {
                if (($item['kind'] ?? null) === 'product' && isset($item['id'])) {
                    return (int) $item['id'];
                }
            }
        }

        return null;
    }

    private function databaseIsAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
