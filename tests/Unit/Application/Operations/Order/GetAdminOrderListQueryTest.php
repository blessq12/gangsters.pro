<?php

namespace Tests\Unit\Application\Operations\Order;

use App\Application\Operations\Order\Contracts\AdminOrderReadRepository;
use App\Application\Operations\Order\Presenter\AdminOrderPresenter;
use App\Application\Operations\Order\Query\GetAdminOrderListQuery;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Services\OrderIdGenerator;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\PaymentInfo;
use PHPUnit\Framework\TestCase;

final class GetAdminOrderListQueryTest extends TestCase
{
    public function test_execute_filters_by_status_via_repository(): void
    {
        $order = (new OrderFactory(new class implements OrderIdGenerator {
            public function generate(): string
            {
                return 'ORD-LIST-1';
            }
        }))->create(
            clientId: null,
            customer: new CustomerSnapshot('Guest', '+79990000002', null, null),
            itemsData: [
                [
                    'productOriginalId' => 1,
                    'name' => 'Roll',
                    'sku' => 'R-1',
                    'listPrice' => 500,
                    'finalPrice' => 500,
                    'quantity' => 1,
                ],
            ],
            paymentInfo: new PaymentInfo('cash', 'unpaid'),
        );

        $repo = new class($order) implements AdminOrderReadRepository {
            public ?string $capturedStatus = null;

            public function __construct(private Order $order) {}

            public function paginate(
                ?string $status = null,
                ?string $dateFrom = null,
                ?string $dateTo = null,
                ?string $phone = null,
                ?int $clientId = null,
                int $page = 1,
                int $perPage = 25,
            ): array {
                $this->capturedStatus = $status;

                return ['items' => [$this->order], 'total' => 1];
            }

            public function findById(string $id): ?Order
            {
                return $this->order;
            }
        };

        $query = new GetAdminOrderListQuery($repo, new AdminOrderPresenter(new OrderPresenter()));
        $result = $query->execute(status: 'new', page: 1, perPage: 10);

        $this->assertSame('new', $repo->capturedStatus);
        $this->assertCount(1, $result['items']);
        $this->assertSame('ORD-LIST-1', $result['items'][0]['id']);
    }
}
