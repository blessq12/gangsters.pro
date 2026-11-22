<?php

namespace Tests\Application\Core;

use App\Application\Delivery\Command\CreateRouteSheetUseCase;
use App\Application\Delivery\Command\SettleStopUseCase;
use App\Application\Delivery\Command\TakeRouteSheetUseCase;
use App\Application\Delivery\DTO\CreateRouteSheetDto;
use App\Application\Delivery\DTO\SettleStopDto;
use App\Application\Delivery\DTO\TakeRouteSheetDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Application\Order\Checkout\CheckoutQuote;
use App\Application\Order\Command\AcceptOrderUseCase;
use App\Application\Order\Command\CancelOrderUseCase;
use App\Application\Order\Command\CompletePickupOrderUseCase;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\Command\ReassignBranchUseCase;
use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\QuoteOrderDto;
use App\Application\Order\DTO\ReassignBranchDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Payment\Presenter\PaymentPresenter;
use App\Application\Production\Command\CompleteWorkUseCase;
use App\Application\Production\Command\StartWorkUseCase;
use App\Application\Production\DTO\CompleteWorkDto;
use App\Application\Production\DTO\StartWorkDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Core\Delivery\Event\OrderAddedToRoute;
use App\Domain\Core\Delivery\Event\RouteSheetCreated;
use App\Domain\Core\Delivery\Event\RouteSheetTaken;
use App\Domain\Core\Delivery\Event\RouteStopSettled;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Event\BranchReassigned;
use App\Domain\Core\Order\Event\OrderCancelled;
use App\Domain\Core\Order\Event\OrderConfirmed;
use App\Domain\Core\Order\Event\OrderPickedUp;
use App\Domain\Core\Order\Event\OrderPlaced;
use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Core\Production\Event\KitchenTicketStarted;
use App\Domain\Generic\Staff\Entity\Employee;
use App\Domain\Generic\Staff\Enum\StaffRole;
use App\Domain\Supporting\Company\Entity\Branch;
use App\Infrastructure\Delivery\Listener\CancelRouteStopOnOrderCancelled;
use App\Infrastructure\Order\Listener\MarkOrderAssignedToRouteOnOrderAddedToRoute;
use App\Infrastructure\Order\Listener\MarkOrderPreparingOnKitchenTicketStarted;
use App\Infrastructure\Order\Listener\MarkOrderReadyOnKitchenTicketCompleted;
use App\Infrastructure\Order\Listener\MarkOrdersInTransitOnRouteSheetTaken;
use App\Infrastructure\Order\Listener\SettleOrderOnRouteStopSettled;
use App\Infrastructure\Production\Listener\CancelKitchenTicketOnOrderCancelled;
use App\Infrastructure\Production\Listener\CreateKitchenTicketOnOrderConfirmed;
use DateTimeImmutable;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Tests\Application\Core\Support\ImmediateTransactionManager;
use Tests\Application\Core\Support\InMemoryEmployeeRepository;
use Tests\Application\Core\Support\InMemoryBranchRepository;
use Tests\Application\Core\Support\InMemoryKitchenTicketRepository;
use Tests\Application\Core\Support\InMemoryOrderRepository;
use Tests\Application\Core\Support\InMemoryPaymentRepository;
use Tests\Application\Core\Support\InMemoryRouteSheetRepository;
use Tests\Application\Core\Support\RecordingDomainEventPublisher;

final class CoreFlowTest extends TestCase
{
    private InMemoryOrderRepository $orders;

    private InMemoryKitchenTicketRepository $tickets;

    private InMemoryRouteSheetRepository $sheets;

    private InMemoryBranchRepository $branches;

    private InMemoryEmployeeRepository $employees;

    private RecordingDomainEventPublisher $events;

    private ImmediateTransactionManager $transaction;

    protected function setUp(): void
    {
        $this->orders = new InMemoryOrderRepository;
        $this->tickets = new InMemoryKitchenTicketRepository;
        $this->sheets = new InMemoryRouteSheetRepository;
        $this->branches = new InMemoryBranchRepository;
        $this->employees = new InMemoryEmployeeRepository;
        $this->transaction = new ImmediateTransactionManager;
        $this->events = new RecordingDomainEventPublisher(
            onEvent: function (object $event): void {
                $this->react($event);
            },
        );

        $this->branches->save(new Branch(2, 1, 'Томск-1', true, [], 'Томск', 'ул. Говорова', '50/1', null, 56.513432, 84.986366));
        $this->branches->save(new Branch(4, 1, 'Томск-2', true, [], 'Томск', 'ул. Ленина', '10', null, 56.48, 84.95));
        $courier = Employee::restore(
            7,
            'Курьер',
            'courier@example.test',
            null,
            StaffRole::Courier,
            'hash',
            true,
            new DateTimeImmutable('2026-11-19T10:00:00Z'),
        );
        $this->employees->save($courier);

        $container = new Container;
        Container::setInstance($container);
        $container->instance('events', new Dispatcher($container));
    }

    public function test_courier_flow_ends_delivered_only_through_events(): void
    {
        $orderId = $this->place('courier-1', 'courier', 20000);

        $this->accept($orderId, 2);
        $this->assertSame(OrderStatus::Confirmed, $this->orders->findById($orderId)->status());
        $this->assertNotNull($this->tickets->findByOrderId($orderId));

        $this->start($orderId);
        $this->assertSame(OrderStatus::Preparing, $this->orders->findById($orderId)->status());

        $this->complete($orderId);
        $this->assertSame(OrderStatus::Ready, $this->orders->findById($orderId)->status());

        $sheetId = $this->releaseRoute([$orderId]);
        $this->assertSame(OrderStatus::AssignedToRoute, $this->orders->findById($orderId)->status());

        $this->take($sheetId, 7);
        $this->assertSame(OrderStatus::InTransit, $this->orders->findById($orderId)->status());

        $this->settle($sheetId, $orderId, RouteStopSettled::OUTCOME_DELIVERED);
        $this->assertSame(OrderStatus::Delivered, $this->orders->findById($orderId)->status());
        $this->assertSame('completed', $this->sheets->findById($sheetId)->status());

        $this->assertSame(
            [
                OrderPlaced::class,
                OrderConfirmed::class,
                KitchenTicketStarted::class,
                KitchenTicketCompleted::class,
                RouteSheetCreated::class,
                OrderAddedToRoute::class,
                RouteSheetTaken::class,
                RouteStopSettled::class,
            ],
            array_map(static fn (object $event): string => $event::class, $this->events->events),
        );
    }

    public function test_start_does_not_move_order_itself(): void
    {
        $this->events = new RecordingDomainEventPublisher(onEvent: static function (): void {});
        $orderId = $this->place('courier-isolated', 'courier', 10000);
        $this->accept($orderId, 2);
        $this->queueTicket($orderId);
        $this->start($orderId);

        $this->assertSame(OrderStatus::Confirmed, $this->orders->findById($orderId)->status());
        $this->assertInstanceOf(KitchenTicketStarted::class, $this->events->events[array_key_last($this->events->events)]);
    }

    public function test_reassign_publishes_branch_reassigned_without_touching_ticket(): void
    {
        $this->events = new RecordingDomainEventPublisher(onEvent: static function (): void {});
        $orderId = $this->place('reassign-1', 'courier', 10000);
        $this->accept($orderId, 2);
        $this->queueTicket($orderId);
        $ticketId = $this->tickets->findByOrderId($orderId)->id();

        (new ReassignBranchUseCase(
            $this->orders,
            $this->branches,
            new AdminOrderPresenter($this->orderPresenter()),
            $this->events,
            $this->transaction,
        ))->execute(new ReassignBranchDto(orderId: $orderId, branchId: 4));

        $order = $this->orders->findById($orderId);
        $this->assertSame(4, $order->branchId());
        $this->assertSame($ticketId, $this->tickets->findByOrderId($orderId)->id());
        $this->assertInstanceOf(BranchReassigned::class, $this->events->events[array_key_last($this->events->events)]);
    }

    public function test_pickup_publishes_order_picked_up(): void
    {
        $orderId = $this->place('pickup-1', 'pickup', 0);
        $this->accept($orderId, 2);
        $this->start($orderId);
        $this->complete($orderId);

        (new CompletePickupOrderUseCase(
            $this->orders,
            new AdminOrderPresenter($this->orderPresenter()),
            $this->events,
            $this->transaction,
        ))->execute($orderId);

        $this->assertSame(OrderStatus::Delivered, $this->orders->findById($orderId)->status());
        $this->assertInstanceOf(OrderPickedUp::class, $this->events->events[array_key_last($this->events->events)]);
    }

    public function test_cancel_publishes_order_cancelled_and_listeners_close_ticket_and_stop(): void
    {
        $orderId = $this->place('cancel-1', 'courier', 15000);
        $this->accept($orderId, 2);
        $this->start($orderId);

        (new CancelOrderUseCase(
            $this->orders,
            new AdminOrderPresenter($this->orderPresenter()),
            $this->events,
            $this->transaction,
        ))->execute($orderId);

        $this->assertSame(OrderStatus::Cancelled, $this->orders->findById($orderId)->status());
        $this->assertSame('cancelled', $this->tickets->findByOrderId($orderId)->status()->value);
        $this->assertInstanceOf(OrderCancelled::class, $this->events->events[array_key_last($this->events->events)]);
    }

    public function test_cancel_on_route_settles_stop_through_listener(): void
    {
        $orderId = $this->place('cancel-route-1', 'courier', 15000);
        $this->accept($orderId, 2);
        $this->start($orderId);
        $this->complete($orderId);
        $sheetId = $this->releaseRoute([$orderId]);

        (new CancelOrderUseCase(
            $this->orders,
            new AdminOrderPresenter($this->orderPresenter()),
            $this->events,
            $this->transaction,
        ))->execute($orderId);

        $this->assertSame(OrderStatus::Cancelled, $this->orders->findById($orderId)->status());
        $this->assertSame('cancelled', $this->sheets->findById($sheetId)->stops()[0]->outcome());
    }

    public function test_written_off_stop_writes_off_order(): void
    {
        $orderId = $this->place('writeoff-1', 'courier', 10000);
        $this->accept($orderId, 2);
        $this->start($orderId);
        $this->complete($orderId);
        $sheetId = $this->releaseRoute([$orderId]);
        $this->take($sheetId, 7);

        $this->settle($sheetId, $orderId, RouteStopSettled::OUTCOME_WRITTEN_OFF, 'клиент не открыл');

        $this->assertSame(OrderStatus::WrittenOff, $this->orders->findById($orderId)->status());
    }

    private function place(string $requestId, string $method, int $feeKopecks): int
    {
        $snapshot = [
            'branch_id' => 2,
            'cart' => [
                'lines' => [[
                    'product_id' => 1,
                    'product_name' => 'Ролл',
                    'quantity' => 1,
                    'unit_price_kopecks' => 25000,
                    'line_type' => 'regular',
                ]],
            ],
            'client' => ['name' => 'Анна', 'phone' => '+79990000000'],
            'delivery' => $method === 'pickup'
                ? ['method' => 'pickup', 'branch_id' => 2]
                : [
                    'method' => 'courier',
                    'branch_id' => 2,
                    'zone_id' => 'center',
                    'in_zone' => true,
                    'delivery_fee_kopecks' => $feeKopecks,
                    'address' => ['street' => 'Ленина', 'house' => '1'],
                ],
            'payment' => ['method' => 'cash'],
            'totals' => [
                'items_total' => 25000,
                'delivery_fee' => $feeKopecks,
                'grand_total' => 25000 + $feeKopecks,
            ],
        ];

        $quote = new class($snapshot) implements CheckoutQuote
        {
            public function __construct(private array $snapshot) {}

            public function execute(QuoteOrderDto $dto): array
            {
                return $this->snapshot;
            }
        };

        $result = (new CreateOrderUseCase(
            $this->orders,
            new OrderPresenter(new InMemoryPaymentRepository, new PaymentPresenter),
            $this->events,
            $this->transaction,
            $quote,
        ))->execute(new CreateOrderDto(
            $requestId,
            new QuoteOrderDto(
                branchId: 2,
                lines: [['product_id' => 1, 'quantity' => 1]],
                deliveryMethod: $method,
                client: ['kind' => 'guest', 'name' => 'Анна', 'phone' => '+79990000000'],
                address: $method === 'pickup' ? null : ['street' => 'Ленина', 'house' => '1'],
                scheduledAt: null,
                paymentMethod: 'cash',
                changeFrom: null,
                giftProductId: null,
                complementSelections: [],
            ),
        ));

        return (int) $result['id'];
    }

    private function accept(int $orderId, int $branchId): void
    {
        (new AcceptOrderUseCase(
            $this->orders,
            new AdminOrderPresenter($this->orderPresenter()),
            $this->events,
            $this->transaction,
        ))->execute(new AcceptOrderDto($orderId));
    }

    private function start(int $orderId): void
    {
        (new StartWorkUseCase(
            $this->tickets,
            $this->orders,
            new KitchenTicketPresenter,
            $this->events,
            $this->transaction,
        ))->execute(new StartWorkDto($orderId));
    }

    private function complete(int $orderId): void
    {
        (new CompleteWorkUseCase(
            $this->tickets,
            $this->orders,
            new KitchenTicketPresenter,
            $this->events,
            $this->transaction,
        ))->execute(new CompleteWorkDto($orderId));
    }

    /**
     * @param  list<int>  $orderIds
     */
    private function releaseRoute(array $orderIds): int
    {
        $result = (new CreateRouteSheetUseCase(
            $this->sheets,
            $this->orders,
            new RouteSheetPresenter($this->orders),
            $this->events,
            $this->transaction,
        ))->execute(new CreateRouteSheetDto($orderIds));

        return (int) $result['id'];
    }

    private function take(int $sheetId, int $courierId): void
    {
        (new TakeRouteSheetUseCase(
            $this->sheets,
            $this->employees,
            new RouteSheetPresenter($this->orders),
            $this->events,
            $this->transaction,
        ))->execute(new TakeRouteSheetDto($sheetId, $courierId));
    }

    private function settle(int $sheetId, int $orderId, string $outcome, ?string $reason = null): void
    {
        (new SettleStopUseCase(
            $this->sheets,
            new RouteSheetPresenter($this->orders),
            $this->events,
            $this->transaction,
        ))->execute(new SettleStopDto($sheetId, $orderId, $outcome, $reason));
    }

    private function queueTicket(int $orderId): void
    {
        $confirmed = null;
        foreach ($this->events->events as $event) {
            if ($event instanceof OrderConfirmed && $event->orderId === $orderId) {
                $confirmed = $event;
            }
        }

        $this->assertInstanceOf(OrderConfirmed::class, $confirmed);
        (new CreateKitchenTicketOnOrderConfirmed($this->orders, $this->tickets))->handle($confirmed);
    }

    private function orderPresenter(): OrderPresenter
    {
        return new OrderPresenter(new InMemoryPaymentRepository, new PaymentPresenter);
    }

    private function react(object $event): void
    {
        if ($event instanceof OrderConfirmed) {
            (new CreateKitchenTicketOnOrderConfirmed($this->orders, $this->tickets))->handle($event);
        }

        if ($event instanceof KitchenTicketStarted) {
            (new MarkOrderPreparingOnKitchenTicketStarted($this->orders))->handle($event);
        }

        if ($event instanceof KitchenTicketCompleted) {
            (new MarkOrderReadyOnKitchenTicketCompleted($this->orders))->handle($event);
        }

        if ($event instanceof OrderAddedToRoute) {
            (new MarkOrderAssignedToRouteOnOrderAddedToRoute($this->orders))->handle($event);
        }

        if ($event instanceof RouteSheetTaken) {
            (new MarkOrdersInTransitOnRouteSheetTaken($this->orders))->handle($event);
        }

        if ($event instanceof RouteStopSettled) {
            (new SettleOrderOnRouteStopSettled($this->orders))->handle($event);
        }

        if ($event instanceof OrderCancelled) {
            (new CancelKitchenTicketOnOrderCancelled($this->tickets))->handle($event);
            (new CancelRouteStopOnOrderCancelled($this->sheets))->handle($event);
        }
    }
}
