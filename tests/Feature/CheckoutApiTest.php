<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CheckoutApiTest extends TestCase
{
    #[Test]
    public function post_checkout_и_get_восстанавливают_черновик(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $createResponse = $this->postJson('/api/checkout');
        $createResponse->assertCreated();
        $createResponse->assertJsonStructure([
            'checkout_id',
            'status',
            'cart',
        ]);

        $checkoutId = $createResponse->json('checkout_id');
        $this->assertNotEmpty($checkoutId);

        $getResponse = $this->getJson('/api/checkout/'.$checkoutId);
        $getResponse->assertOk();
        $getResponse->assertJsonPath('checkout_id', $checkoutId);
        $getResponse->assertJsonPath('status', 'draft');
    }

    #[Test]
    public function get_checkout_для_неизвестного_id_возвращает_404(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $response = $this->getJson('/api/checkout/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }

    #[Test]
    public function patch_cart_возвращает_benefits_progress(): void
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

        $patchResponse = $this->patchJson('/api/checkout/'.$checkoutId.'/cart', [
            'product_id' => $productId,
            'quantity' => 1,
        ]);

        $patchResponse->assertOk();
        $patchResponse->assertJsonStructure([
            'benefits_progress' => [
                'delivery',
                'gift',
                'complement',
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
