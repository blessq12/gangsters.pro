<?php

namespace Tests\Unit\Application\Operations\Order;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Command\ChangeOrderStatusUseCase;
use App\Application\Operations\Order\DTO\ChangeOrderStatusDTO;
use App\Application\Operations\Order\Presenter\AdminOrderPresenter;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\Services\OrderIdGenerator;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Shared\Events\IntegrationEventBus;
use PHPUnit\Framework\TestCase;

final class ChangeOrderStatusUseCaseTest extends TestCase
{
    public function test_execute_marks_preparing(): void
    {
        $order = $this->makeOrder();
        $repo = $this->repositoryStub($order);

        $useCase = new ChangeOrderStatusUseCase(
            $repo,
            new AdminOrderPresenter(new OrderPresenter()),
            new class implements IntegrationEventBus {
                public function publish(object $event): void {}

                public function publishAll(iterable $events): void
                {
                    foreach ($events as $event) {
                        $this->publish($event);
                    }
                }
            },
        );

        $result = $useCase->execute(new ChangeOrderStatusDTO($order->getId(), 'preparing'));

        $this->assertSame('preparing', $result['status']);
        $this->assertSame(1, $repo->saveCalls);
    }

    public function test_execute_rejects_unknown_status(): void
    {
        $order = $this->makeOrder();
        $repo = $this->repositoryStub($order);

        $useCase = new ChangeOrderStatusUseCase(
            $repo,
            new AdminOrderPresenter(new OrderPresenter()),
            new class implements IntegrationEventBus {
                public function publish(object $event): void {}

                public function publishAll(iterable $events): void
                {
                    foreach ($events as $event) {
                        $this->publish($event);
                    }
                }
            },
        );

        $this->expectException(ApiException::class);

        $useCase->execute(new ChangeOrderStatusDTO($order->getId(), 'cancelled'));
    }

    private function makeOrder(): Order
    {
        $factory = new OrderFactory(new class implements OrderIdGenerator {
            public function generate(): string
            {
                return 'ORD-OPS-1';
            }
        });

        return $factory->create(
            clientId: 1,
            customer: new CustomerSnapshot('Test', '+79990000001', null, null),
            itemsData: [
                [
                    'productOriginalId' => 1,
                    'name' => 'Roll',
                    'sku' => 'R-1',
                    'listPrice' => 1000,
                    'finalPrice' => 1000,
                    'quantity' => 1,
                ],
            ],
            paymentInfo: new PaymentInfo('card', 'unpaid'),
        );
    }

    private function repositoryStub(Order $order): OrderRepositoryInterface
    {
        return new class($order) implements OrderRepositoryInterface {
            public int $saveCalls = 0;

            public function __construct(private Order $order) {}

            public function getById(string $id): Order
            {
                return $this->order;
            }

            public function findByClientId(int $clientId): array
            {
                return [];
            }

            public function save(Order $order): void
            {
                $this->order = $order;
                $this->saveCalls++;
            }

            public function delete(string $id): void {}
        };
    }
}
