<?php

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Services\OrderIdGenerator;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\OrderStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderFactoryTest extends TestCase
{
    public function test_create_calculates_money_fields_from_list_and_final_prices(): void
    {
        $factory = new OrderFactory(new class implements OrderIdGenerator {
            public function generate(): string
            {
                return 'ORD-TEST-1';
            }
        });

        $order = $factory->create(
            clientId: 101,
            customer: new CustomerSnapshot('Client', '+79990000000', 'client@example.com', null),
            itemsData: [
                [
                    'productOriginalId' => 10,
                    'name' => 'Item A',
                    'sku' => 'A-1',
                    'listPrice' => 1000,
                    'finalPrice' => 800,
                    'quantity' => 2,
                ],
                [
                    'productOriginalId' => 11,
                    'name' => 'Item B',
                    'sku' => 'B-1',
                    'listPrice' => 500,
                    'finalPrice' => 500,
                    'quantity' => 1,
                ],
            ],
        );

        $this->assertSame('ORD-TEST-1', $order->getId());
        $this->assertSame(101, $order->getClientId());
        $this->assertSame(2500, $order->getSubtotal());
        $this->assertSame(400, $order->getDiscountTotal());
        $this->assertSame(2100, $order->getTotal());

        $items = $order->getItems();
        $this->assertCount(2, $items);

        $this->assertSame(1000, $items[0]->getUnitPrice());
        $this->assertSame(2000, $items[0]->getRowSubtotal());
        $this->assertSame(400, $items[0]->getRowDiscount());
        $this->assertSame(1600, $items[0]->getRowTotal());
    }

    public function test_rebuild_order_keeps_identity_and_recalculates_totals(): void
    {
        $factory = new OrderFactory(new class implements OrderIdGenerator {
            public function generate(): string
            {
                return 'IGNORED-BY-REBUILD';
            }
        });

        $createdAt = new DateTimeImmutable('2026-01-10 10:00:00');

        $order = $factory->rebuildOrder(
            id: 'ORD-42',
            clientId: 777,
            customer: new CustomerSnapshot('Client', '+79995554433', null, ['street' => 'Lenina']),
            status: OrderStatus::new(),
            itemsData: [
                [
                    'productOriginalId' => 99,
                    'name' => 'Item C',
                    'sku' => 'C-1',
                    'listPrice' => 1200,
                    'finalPrice' => 900,
                    'quantity' => 3,
                ],
            ],
            deliveryInfo: null,
            paymentInfo: null,
            createdAt: $createdAt,
        );

        $this->assertSame('ORD-42', $order->getId());
        $this->assertSame(777, $order->getClientId());
        $this->assertEquals($createdAt, $order->getCreatedAt());
        $this->assertSame(3600, $order->getSubtotal());
        $this->assertSame(900, $order->getDiscountTotal());
        $this->assertSame(2700, $order->getTotal());
    }
}

