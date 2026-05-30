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
        $order = $this->sampleOrder();
        $repo = new CapturingAdminOrderReadRepository($order);

        $query = new GetAdminOrderListQuery($repo, new AdminOrderPresenter(new OrderPresenter()));
        $result = $query->execute(status: 'new', page: 1, perPage: 10);

        $this->assertSame('new', $repo->capturedStatus);
        $this->assertCount(1, $result['items']);
        $this->assertSame('ORD-LIST-1', $result['items'][0]['id']);
    }

    public function test_execute_passes_search_payment_status_and_dates(): void
    {
        $order = $this->sampleOrder();
        $repo = new CapturingAdminOrderReadRepository($order);

        $query = new GetAdminOrderListQuery($repo, new AdminOrderPresenter(new OrderPresenter()));
        $query->execute(
            status: 'preparing',
            dateFrom: '2026-01-01',
            dateTo: '2026-01-31',
            search: '7999',
            paymentStatus: 'paid',
            clientId: 5,
            page: 2,
            perPage: 50,
        );

        $this->assertSame('preparing', $repo->capturedStatus);
        $this->assertSame('2026-01-01', $repo->capturedDateFrom);
        $this->assertSame('2026-01-31', $repo->capturedDateTo);
        $this->assertSame('7999', $repo->capturedSearch);
        $this->assertSame('paid', $repo->capturedPaymentStatus);
        $this->assertSame(5, $repo->capturedClientId);
    }

    private function sampleOrder(): Order
    {
        return (new OrderFactory(new class implements OrderIdGenerator {
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
    }
}

final class CapturingAdminOrderReadRepository implements AdminOrderReadRepository
{
    public ?string $capturedStatus = null;

    public ?string $capturedDateFrom = null;

    public ?string $capturedDateTo = null;

    public ?string $capturedSearch = null;

    public ?string $capturedPaymentStatus = null;

    public ?int $capturedClientId = null;

    public function __construct(private readonly Order $order) {}

    public function paginate(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        ?string $paymentStatus = null,
        ?int $clientId = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $this->capturedStatus = $status;
        $this->capturedDateFrom = $dateFrom;
        $this->capturedDateTo = $dateTo;
        $this->capturedSearch = $search;
        $this->capturedPaymentStatus = $paymentStatus;
        $this->capturedClientId = $clientId;

        return ['items' => [$this->order], 'total' => 1];
    }

    public function findById(string $id): ?Order
    {
        return $this->order;
    }
}
