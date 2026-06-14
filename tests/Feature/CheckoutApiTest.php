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
