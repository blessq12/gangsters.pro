<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CheckoutConfirmCreatesOrderTest extends TestCase
{
    #[Test]
    public function confirm_checkout_создаёт_заказ(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $productId = $this->resolveFirstProductId();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров.');
        }

        $createResponse = $this->postJson('/api/checkout');
        $createResponse->assertCreated();
        $checkoutId = (string) $createResponse->json('checkout_id');

        $this->patchJson('/api/checkout/'.$checkoutId.'/cart', [
            'product_id' => $productId,
            'quantity' => 1,
        ])->assertOk();

        $this->patchJson('/api/checkout/'.$checkoutId.'/client', [
            'name' => 'Тест Гость',
            'phone' => '+79990001122',
        ])->assertOk();

        $this->patchJson('/api/checkout/'.$checkoutId.'/delivery', [
            'method' => 'pickup',
        ])->assertOk();

        $this->patchJson('/api/checkout/'.$checkoutId.'/payment', [
            'method' => 'cash',
        ])->assertOk();

        $confirmResponse = $this->postJson('/api/checkout/'.$checkoutId.'/confirm');
        $confirmResponse->assertOk();
        $confirmResponse->assertJsonPath('status', 'confirmed');
        $confirmResponse->assertJsonStructure([
            'order' => [
                'id',
                'checkout_id',
                'status',
            ],
        ]);
        $confirmResponse->assertJsonPath('order.checkout_id', $checkoutId);
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
