<?php

namespace Tests\Unit\Application\Order;

use App\Application\Order\Service\RecalculateOrderTotalsFromItems;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Order\Model\ORD_OrderItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RecalculateOrderTotalsFromItemsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('ORD_orders') || ! Schema::hasTable('ORD_order_items')) {
            $this->markTestSkipped('Нет таблиц заказов.');
        }
    }

    public function test_recalculates_header_totals_from_items(): void
    {
        $order = ORD_Order::query()->create([
            'id' => (string) Str::uuid(),
            'status' => 'new',
            'subtotal' => 0,
            'discount_total' => 0,
            'total' => 0,
            'customer_name' => 'Тест',
            'customer_phone' => '+79990001122',
            'delivery_method' => 'courier',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
        ]);

        ORD_OrderItem::query()->create([
            'order_id' => $order->id,
            'product_name' => 'Ролл',
            'product_sku' => 'r1',
            'quantity' => 2,
            'unit_price' => 50000,
            'row_subtotal' => 100000,
            'row_discount' => 10000,
            'row_total' => 90000,
            'product_list_price' => 50000,
            'product_final_price' => 50000,
        ]);

        ORD_OrderItem::query()->create([
            'order_id' => $order->id,
            'product_name' => 'Соус',
            'product_sku' => 's1',
            'quantity' => 1,
            'unit_price' => 5000,
            'row_subtotal' => 5000,
            'row_discount' => 0,
            'row_total' => 5000,
            'product_list_price' => 5000,
            'product_final_price' => 5000,
        ]);

        app(RecalculateOrderTotalsFromItems::class)->recalculate($order);

        $fresh = $order->fresh();
        $this->assertSame(105000, $fresh->subtotal);
        $this->assertSame(10000, $fresh->discount_total);
        $this->assertSame(95000, $fresh->total);

        $order->items()->delete();
        $order->delete();
    }
}
