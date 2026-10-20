<?php

namespace Tests\Unit\Integration;

use App\Domain\Order\Event\OrderCreated;
use App\Infrastructure\Integration\Frontpad\FrontpadOrderMapper;
use DateTimeImmutable;
use Tests\TestCase;

final class FrontpadOrderMapperWebhookTest extends TestCase
{
    public function test_append_webhook_includes_secret_and_all_statuses(): void
    {
        config([
            'app.url' => 'https://shop.test',
            'frontpad.secret' => 's3cret',
            'frontpad.hook_url' => null,
            'frontpad.hook_status' => [1, 13, 3, 12, 4, 11, 10],
        ]);

        $request = (new FrontpadOrderMapper())->toRequest($this->minimalEvent());

        $this->assertSame(
            'https://shop.test/api/orders/update?secret=s3cret',
            $request['hook_url'],
        );
        $this->assertSame([1, 13, 3, 12, 4, 11, 10], $request['hook_status']);
    }

    private function minimalEvent(): OrderCreated
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
                'method' => 'pickup',
                'persons' => 1,
                'comment' => null,
            ],
            payment: [
                'method' => 'cash',
            ],
            occurredAt: new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );
    }
}
