<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Illuminate\Support\Facades\DB;

final class OrderDeliveryFeeTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->disableCookieEncryption();
        $this->withCredentials();
        $this->skipUnlessTablesExist([
            'companies',
            'SHP_shopping_sessions',
            'SHP_shopping_cart_lines',
            'ORD_orders',
            'ORD_order_items',
            'PRD_products',
            'PRD_category_product',
            'PRD_categories',
        ]);
    }

    public function test_create_order_persists_delivery_fee_for_courier_below_threshold(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $restore = $this->withCompanyDeliveryTerms(10_000_00, 150_00);

        try {
            $cart = $this->postJson('/api/shopping/cart/items', [
                'product_id' => $productId,
                'quantity' => 1,
            ])->assertOk();

            $cookieName = (string) config('shopping.session_cookie');
            $sessionPublicId = $cart->json('data.session.public_id');

            $state = $this->withCookie($cookieName, $sessionPublicId)
                ->patchJson('/api/shopping/checkout-draft', [
                    'delivery_info' => ['method' => 'courier'],
                ])
                ->assertOk();

            $expectedFee = (int) $state->json('data.delivery_pricing.delivery_fee_kopecks');
            $expectedGrand = (int) $state->json('data.delivery_pricing.grand_total_kopecks');

            $orderResponse = $this->withCookie($cookieName, $sessionPublicId)
                ->postJson('/api/order', [
                    'items' => [],
                    'delivery_method' => 'courier',
                    'delivery_address' => [
                        'street' => 'Ленина',
                        'house' => '1',
                    ],
                    'payment_method' => 'cash',
                    'customer_name' => 'Delivery Fee Guest',
                    'customer_phone' => $this->uniquePhone(),
                ])
                ->assertCreated();

            $orderId = $orderResponse->json('id');
            $this->assertIsString($orderId);

            $orderResponse->assertJsonPath('delivery_fee', $expectedFee / 100);
            $orderResponse->assertJsonPath('total', $expectedGrand / 100);

            $model = ORD_Order::query()->find($orderId);
            $this->assertNotNull($model);
            $this->assertSame($expectedFee, (int) $model->delivery_fee_kopecks);
            $this->assertSame($expectedGrand, (int) $model->total);
            $this->assertIsArray($model->delivery_pricing_snapshot);
            $this->assertSame('courier', $model->delivery_pricing_snapshot['method'] ?? null);
        } finally {
            $restore();
        }
    }

    public function test_create_order_pickup_has_zero_delivery_fee(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $restore = $this->withCompanyDeliveryTerms(10_000_00, 150_00);

        try {
            $cart = $this->postJson('/api/shopping/cart/items', [
                'product_id' => $productId,
                'quantity' => 1,
            ])->assertOk();

            $cookieName = (string) config('shopping.session_cookie');
            $sessionPublicId = $cart->json('data.session.public_id');

            $this->withCookie($cookieName, $sessionPublicId)
                ->postJson('/api/order', [
                    'items' => [],
                    'delivery_method' => 'pickup',
                    'payment_method' => 'cash',
                    'customer_name' => 'Pickup Guest',
                    'customer_phone' => $this->uniquePhone(),
                ])
                ->assertCreated()
                ->assertJsonPath('delivery_fee', 0);
        } finally {
            $restore();
        }
    }

    public function test_client_cannot_submit_delivery_fee_kopecks(): void
    {
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->postJson('/api/order', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'delivery_fee_kopecks' => 999_00,
            'customer_name' => 'Hacker',
            'customer_phone' => $this->uniquePhone(),
        ])->assertStatus(422);
    }

    /**
     * @return callable(): void
     */
    private function withCompanyDeliveryTerms(int $thresholdKopecks, int $feeKopecks): callable
    {
        $company = SYS_Company::query()->first();
        if ($company === null) {
            $this->markTestSkipped('Нет записи companies.');
        }

        $originalThreshold = $company->min_order_amount_kopecks;
        $originalFee = $company->delivery_fee_kopecks;

        $company->min_order_amount_kopecks = $thresholdKopecks;
        $company->delivery_fee_kopecks = $feeKopecks;
        $company->save();

        return static function () use ($company, $originalThreshold, $originalFee): void {
            $company->min_order_amount_kopecks = $originalThreshold;
            $company->delivery_fee_kopecks = $originalFee;
            $company->save();
        };
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
