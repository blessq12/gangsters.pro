<?php

namespace Tests\Unit\Integration;

use App\Domain\Order\Event\OrderCreated;
use App\Infrastructure\Integration\Frontpad\FrontpadOrderMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\TestCase;

final class FrontpadOrderMapperPersonTest extends TestCase
{
    public function test_maps_persons_from_delivery_snapshot(): void
    {
        $mapper = new FrontpadOrderMapper();

        $request = $mapper->toRequest($this->event(persons: 4));

        $this->assertSame(4, $request['person']);
    }

    public function test_rejects_missing_persons(): void
    {
        $mapper = new FrontpadOrderMapper();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('delivery.persons обязателен');

        $mapper->toRequest($this->event(persons: null));
    }

    private function event(?int $persons): OrderCreated
    {
        $delivery = [
            'method' => 'pickup',
            'comment' => null,
        ];

        if ($persons !== null) {
            $delivery['persons'] = $persons;
        }

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
            delivery: $delivery,
            payment: [
                'method' => 'cash',
            ],
            occurredAt: new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );
    }
}
