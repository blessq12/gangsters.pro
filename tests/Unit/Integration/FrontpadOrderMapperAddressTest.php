<?php

namespace Tests\Unit\Integration;

use App\Domain\Order\Event\OrderCreated;
use App\Infrastructure\Integration\Frontpad\FrontpadOrderMapper;
use DateTimeImmutable;
use Tests\TestCase;

final class FrontpadOrderMapperAddressTest extends TestCase
{
    public function test_maps_floor_to_et(): void
    {
        $mapper = new FrontpadOrderMapper();

        $request = $mapper->toRequest($this->courierEvent([
            'street' => 'Мира',
            'house' => '17',
            'entrance' => '1',
            'floor' => '9',
            'apartment' => '42',
        ]));

        $this->assertSame('Мира', $request['street']);
        $this->assertSame('17', $request['home']);
        $this->assertSame('1', $request['pod']);
        $this->assertSame('9', $request['et']);
        $this->assertSame('42', $request['apart']);
    }

    public function test_truncates_floor_to_two_chars(): void
    {
        $mapper = new FrontpadOrderMapper();

        $request = $mapper->toRequest($this->courierEvent([
            'street' => 'Мира',
            'house' => '17',
            'floor' => '12а',
        ]));

        $this->assertSame('12', $request['et']);
    }

    public function test_omits_et_when_floor_empty(): void
    {
        $mapper = new FrontpadOrderMapper();

        $request = $mapper->toRequest($this->courierEvent([
            'street' => 'Мира',
            'house' => '17',
            'floor' => '',
        ]));

        $this->assertArrayNotHasKey('et', $request);
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function courierEvent(array $address): OrderCreated
    {
        return new OrderCreated(
            orderId: 42,
            source: 'site',
            checkoutId: 'test-checkout',
            partnerCode: null,
            externalOrderId: null,
            cart: [
                'lines' => [
                    [
                        'product_id' => 1,
                        'quantity' => 1,
                        'sku' => '1001',
                        'unit_price_rubles' => 500,
                    ],
                ],
            ],
            client: [
                'name' => 'Тест',
                'phone' => '+7 (900) 111-22-33',
                'email' => '',
            ],
            delivery: [
                'method' => 'courier',
                'persons' => 1,
                'comment' => null,
                'address' => $address,
            ],
            payment: [
                'method' => 'cash',
            ],
            occurredAt: new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );
    }
}
