<?php

namespace Tests\Unit\Integration;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Integration\Frontpad\FrontpadApiClient;
use App\Infrastructure\Integration\Frontpad\FrontpadOrderMapper;
use App\Infrastructure\Integration\Frontpad\HttpFrontpadOrderExporter;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class HttpFrontpadOrderExporterPersistTest extends TestCase
{
    public function test_persists_frontpad_order_id_after_successful_export(): void
    {
        config([
            'frontpad.enabled' => true,
            'frontpad.secret' => 'test-secret',
            'frontpad.endpoint' => 'https://frontpad.test/new_order',
        ]);

        Http::fake([
            'frontpad.test/*' => Http::response([
                'result' => 'success',
                'order_id' => 999,
            ]),
        ]);

        $order = Order::restore(
            id: 42,
            source: 'site',
            checkoutId: 'chk-1',
            partnerCode: null,
            externalOrderId: null,
            status: OrderStatus::New->value,
            cart: ['lines' => [['sku' => '1001', 'quantity' => 1, 'unit_price_rubles' => 100]]],
            client: ['name' => 'Тест', 'phone' => '+79001112233', 'email' => ''],
            delivery: ['method' => 'pickup', 'persons' => 1],
            payment: ['method' => 'cash'],
            createdAt: new DateTimeImmutable('2026-10-19 12:00:00'),
            frontpadOrderId: null,
        );

        $repository = $this->createMock(OrderRepository::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($order);
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $saved): bool {
                return $saved->id() === 42 && $saved->frontpadOrderId() === '999';
            }));

        $exporter = new HttpFrontpadOrderExporter(
            new FrontpadOrderMapper(),
            new FrontpadApiClient(),
            $repository,
        );

        $exporter->export($this->orderCreatedEvent());
    }

    private function orderCreatedEvent(): OrderCreated
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
