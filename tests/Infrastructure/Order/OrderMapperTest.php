<?php

namespace Tests\Infrastructure\Order;

use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\OrderLineModel;
use App\Infrastructure\Order\Model\OrderModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

final class OrderMapperTest extends TestCase
{
    public function test_старый_курьерский_заказ_без_улицы_дома_и_зоны_читается(): void
    {
        $row = new OrderModel;
        $row->timestamps = false;
        $row->setRawAttributes([
            'id' => 7,
            'source' => 'site',
            'status' => 'delivered',
            'client_name' => 'Анна',
            'client_phone' => '79990000000',
            'delivery_type' => 'courier',
            'delivery_zone_id' => null,
            'delivery_fee_kopecks' => 0,
            'delivery_street' => null,
            'delivery_house' => null,
            'created_at' => new DateTimeImmutable('2024-05-01 12:00:00'),
        ], true);

        $line = new OrderLineModel;
        $line->setRawAttributes([
            'product_id' => 1,
            'name' => 'Ролл',
            'unit_price_kopecks' => 10000,
            'quantity' => 1,
            'line_type' => 'regular',
        ], true);
        $row->setRelation('lines', new Collection([$line]));

        $order = (new OrderMapper)->toDomain($row);

        self::assertSame(7, $order->id());
        self::assertTrue($order->delivery()->isCourier());
        self::assertSame('', $order->delivery()->address()?->street());
        self::assertSame('', $order->delivery()->address()?->house());
        self::assertNull($order->delivery()->zoneId());
    }
}
