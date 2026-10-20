<?php

namespace Tests\Feature\Order;

use App\Domain\Order\Event\OrderStatusChanged;
use App\Infrastructure\Order\Model\ORD_Order;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\ApiTestCase;

final class FrontpadStatusWebhookTest extends ApiTestCase
{
    public function test_probe_get_order_update_returns_200_without_secret(): void
    {
        $this->getJson('/api/order/update')->assertOk()->assertJson(['ok' => true]);
    }

    public function test_probe_get_orders_update_alias_returns_200(): void
    {
        $this->getJson('/api/orders/update')->assertOk()->assertJson(['ok' => true]);
    }

    public function test_probe_post_without_change_status_returns_200(): void
    {
        $this->postJson('/api/order/update', [])->assertOk()->assertJson(['ok' => true]);
    }

    public function test_updates_status_without_secret_as_per_frontpad_docs(): void
    {
        $this->seedOrderWithFrontpad('12345678', 'new');
        Event::fake([OrderStatusChanged::class]);

        $this->postJson('/api/orders/update', [
            'action' => 'change_status',
            'order_id' => 12345678,
            'status' => 3,
            'datetime' => '2020-08-21 12:03:00',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('ORD_orders', [
            'frontpad_order_id' => '12345678',
            'status' => 'preparing',
        ]);
        Event::assertDispatched(OrderStatusChanged::class);
    }

    public function test_updates_status_from_form_post_like_frontpad(): void
    {
        $this->seedOrderWithFrontpad('49670745', 'new');
        Event::fake([OrderStatusChanged::class]);

        $this->post('/api/order/update', [
            'action' => 'change_status',
            'order_id' => '49670745',
            'status' => '3',
            'datetime' => '2026-10-20 13:24:11',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('ORD_orders', [
            'frontpad_order_id' => '49670745',
            'status' => 'preparing',
        ]);
        Event::assertDispatched(OrderStatusChanged::class);
    }

    public function test_form_post_validation_failure_returns_json_not_redirect(): void
    {
        $response = $this->post('/api/orders/update', [
            'action' => 'change_status',
            'status' => 3,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $this->assertNotEquals(302, $response->status());
    }

    public function test_unknown_frontpad_status_returns_422(): void
    {
        $this->seedOrderWithFrontpad('11111111', 'new');

        $this->postJson('/api/orders/update', [
            'action' => 'change_status',
            'order_id' => 11111111,
            'status' => 99,
        ])->assertStatus(422);
    }

    public function test_unknown_order_returns_404(): void
    {
        $this->postJson('/api/orders/update', [
            'action' => 'change_status',
            'order_id' => 99999999,
            'status' => 3,
        ])->assertNotFound();
    }

    public function test_idempotent_same_status_no_second_event(): void
    {
        $this->seedOrderWithFrontpad('22222222', 'preparing');
        Event::fake([OrderStatusChanged::class]);

        $this->postJson('/api/orders/update', [
            'action' => 'change_status',
            'order_id' => 22222222,
            'status' => 3,
        ])->assertOk()->assertJson(['ok' => true]);

        Event::assertNotDispatched(OrderStatusChanged::class);
    }

    private function seedOrderWithFrontpad(string $frontpadOrderId, string $status): void
    {
        ORD_Order::query()->create([
            'source' => 'site',
            'checkout_id' => (string) Str::uuid(),
            'frontpad_order_id' => $frontpadOrderId,
            'status' => $status,
            'client_id' => null,
            'total_rubles' => 100,
            'cart_snapshot' => ['lines' => []],
            'client_snapshot' => [],
            'delivery_snapshot' => [],
            'payment_snapshot' => [],
            'created_at' => now(),
        ]);
    }
}
