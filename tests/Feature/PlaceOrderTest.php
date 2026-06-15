<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PlaceOrderTest extends TestCase
{
    #[Test]
    public function place_order_создаёт_заказ(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $productId = $this->resolveFirstProductId();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров.');
        }

        $clientRequestId = (string) Str::uuid();

        $response = $this->postJson('/api/orders', [
            'client_request_id' => $clientRequestId,
            'cart' => [
                'lines' => [
                    ['product_id' => $productId, 'quantity' => 1],
                ],
            ],
            'client' => [
                'name' => 'Тест Гость',
                'phone' => '+79990001122',
            ],
            'delivery' => [
                'method' => 'pickup',
            ],
            'payment' => [
                'method' => 'cash',
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('order.client_request_id', $clientRequestId);
        $response->assertJsonStructure([
            'order' => [
                'id',
                'status',
            ],
        ]);
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
