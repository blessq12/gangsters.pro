<?php

namespace Tests\Unit\Application\Order;

use App\Application\Order\Command\MarkOrderPaidService;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\Services\OrderIdGenerator;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Shared\Events\DomainEvent;
use App\Shared\Events\DomainEventBus;
use App\Shared\Events\IntegrationEvent;
use App\Shared\Events\IntegrationEventBus;
use PHPUnit\Framework\TestCase;

final class MarkOrderPaidServiceTest extends TestCase
{
    public function test_execute_sets_paid_status_and_publishes_event(): void
    {
        $factory = new OrderFactory(new class implements OrderIdGenerator {
            public function generate(): string
            {
                return 'ORD-PAY-1';
            }
        });

        $order = $factory->create(
            clientId: 42,
            customer: new CustomerSnapshot('Client', '+79990000000', 'client@example.com', null),
            itemsData: [
                [
                    'productOriginalId' => 10,
                    'name' => 'Roll',
                    'sku' => 'R-1',
                    'listPrice' => 1000,
                    'finalPrice' => 1000,
                    'quantity' => 1,
                ],
            ],
            paymentInfo: new PaymentInfo('card', 'unpaid'),
        );

        $repo = new class($order) implements OrderRepositoryInterface {
            public function __construct(private Order $order) {}

            public int $saveCalls = 0;

            public function getById(string $id): Order
            {
                return $this->order;
            }

            public function findByClientId(int $clientId): array
            {
                return [$this->order];
            }

            public function save(Order $order): void
            {
                $this->order = $order;
                $this->saveCalls++;
            }

            public function delete(string $id): void {}
        };

        $domainEvents = new class implements DomainEventBus {
            /** @var array<int, DomainEvent> */
            public array $events = [];

            public function publish(DomainEvent $event): void
            {
                $this->events[] = $event;
            }

            public function publishAll(iterable $events): void
            {
                foreach ($events as $event) {
                    $this->publish($event);
                }
            }
        };

        $integrationEvents = new class implements IntegrationEventBus {
            /** @var array<int, IntegrationEvent> */
            public array $events = [];

            public function publish(IntegrationEvent $event): void
            {
                $this->events[] = $event;
            }

            public function publishAll(iterable $events): void
            {
                foreach ($events as $event) {
                    $this->publish($event);
                }
            }
        };

        $service = new MarkOrderPaidService(
            $repo,
            new OrderPresenter(),
            $domainEvents,
            $integrationEvents,
        );

        $result = $service->execute('ORD-PAY-1');

        $this->assertSame('paid', $result['payment']['status']);
        $this->assertSame(1, $repo->saveCalls);
        $this->assertCount(1, $domainEvents->events);
        $this->assertCount(1, $integrationEvents->events);
    }
}
