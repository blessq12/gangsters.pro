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
use App\Application\Order\Checkout\CheckoutRulesVersion;
use App\Application\Order\Command\AcceptOrderUseCase;
use App\Application\Order\Command\CancelOrderUseCase;
use App\Application\Order\Command\CompletePickupOrderUseCase;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\QuoteOrderDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Production\Command\MarkTicketLineDoneUseCase;
use App\Application\Production\Command\MarkTicketLinePackedUseCase;
use App\Application\Production\Command\StartWorkUseCase;
use App\Application\Production\DTO\MarkTicketLineDoneDto;
use App\Application\Production\DTO\MarkTicketLinePackedDto;
use App\Application\Production\DTO\StartWorkDto;
use App\Application\Production\Presenter\KitchenTicketPresenter;
use App\Domain\Core\Delivery\Event\OrderAddedToRoute;
use App\Domain\Core\Delivery\Event\RouteSheetCreated;
use App\Domain\Core\Delivery\Event\RouteSheetTaken;
use App\Domain\Core\Delivery\Event\RouteStopSettled;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Event\OrderCancelled;
use App\Domain\Core\Order\Event\OrderConfirmed;
use App\Domain\Core\Order\Event\OrderPickedUp;
use App\Domain\Core\Order\Event\OrderPlaced;
use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Core\Production\Event\KitchenTicketLineDone;
use App\Domain\Core\Production\Event\KitchenTicketLinePacked;
use App\Domain\Core\Production\Event\KitchenTicketStarted;
use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Staff\Entity\Employee;
use App\Domain\Supporting\Staff\Enum\StaffRole;
use App\Infrastructure\Delivery\Listener\CancelRouteStopOnOrderCancelled;
use App\Infrastructure\Order\Listener\MarkOrderAssignedToRouteOnOrderAddedToRoute;
use App\Infrastructure\Order\Listener\MarkOrderPackedOnKitchenTicketFullyPacked;
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
use Tests\Application\Core\Support\InMemoryBranchRepository;
use Tests\Application\Core\Support\InMemoryDeliveryCourierLookupPort;
use Tests\Application\Core\Support\InMemoryDeliveryOrderStopSnapshotPort;
use Tests\Application\Core\Support\InMemoryDeliveryRouteOrderEligibilityPort;
use Tests\Application\Core\Support\InMemoryEmployeeRepository;
use Tests\Application\Core\Support\InMemoryKitchenTicketRepository;
use Tests\Application\Core\Support\InMemoryOrderAuditRepository;
use Tests\Application\Core\Support\InMemoryOrderPaymentSnapshotPort;
use Tests\Application\Core\Support\InMemoryOrderRepository;
use Tests\Application\Core\Support\InMemoryPaymentRepository;
use Tests\Application\Core\Support\InMemoryProductionOrderSnapshotPort;
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
            branchId: 2,
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
        $this->assertSame(OrderStatus::Preparing, $this->orders->findById($orderId)->status());
        $this->assertNotNull($this->tickets->findByOrderId($orderId));

        $this->complete($orderId);
        $this->assertSame(OrderStatus::Ready, $this->orders->findById($orderId)->status());

        $this->pack($orderId);
        $this->assertSame(OrderStatus::Packed, $this->orders->findById($orderId)->status());

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
                KitchenTicketLineDone::class,
                KitchenTicketCompleted::class,
                KitchenTicketLinePacked::class,
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

        $this->assertSame(OrderStatus::Preparing, $this->orders->findById($orderId)->status());
        $this->assertInstanceOf(KitchenTicketStarted::class, $this->events->events[array_key_last($this->events->events)]);
    }

    public function test_pickup_publishes_order_picked_up(): void
    {
        $orderId = $this->place('pickup-1', 'pickup', 0);
        $this->accept($orderId, 2);
        $this->complete($orderId);
        $this->pack($orderId);

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
        $this->complete($orderId);
        $this->pack($orderId);
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
        $this->complete($orderId);
        $this->pack($orderId);
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
                    'address' => [
                        'street' => 'Ленина',
                        'house' => '1',
                        'lat' => 56.49771,
                        'lng' => 84.97437,
                    ],
                    'latitude' => 56.49771,
                    'longitude' => 84.97437,
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
            new OrderPresenter(new InMemoryOrderPaymentSnapshotPort(new InMemoryPaymentRepository)),
            $this->events,
            $this->transaction,
            $quote,
            new class implements CheckoutRulesVersion
            {
                public function forBranch(int $branchId): string
                {
                    return 'v1';
                }
            },
        ))->execute(new CreateOrderDto(
            $requestId,
            new QuoteOrderDto(
                branchId: 2,
                lines: [['product_id' => 1, 'quantity' => 1]],
                deliveryMethod: $method,
                client: ['kind' => 'guest', 'name' => 'Анна', 'phone' => '+79990000000'],
                address: $method === 'pickup' ? null : [
                    'street' => 'Ленина',
                    'house' => '1',
                    'lat' => 56.49771,
                    'lng' => 84.97437,
                ],
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

    private function productionOrders(): InMemoryProductionOrderSnapshotPort
    {
        return new InMemoryProductionOrderSnapshotPort($this->orders);
    }

    private function start(int $orderId): void
    {
        (new StartWorkUseCase(
            $this->tickets,
            $this->productionOrders(),
            new KitchenTicketPresenter,
            $this->events,
            $this->transaction,
        ))->execute(new StartWorkDto(orderId: $orderId, employeeId: 1));
    }

    private function complete(int $orderId): void
    {
        $ticket = $this->tickets->findByOrderId($orderId);
        $this->assertNotNull($ticket);

        $useCase = new MarkTicketLineDoneUseCase(
            $this->tickets,
            $this->productionOrders(),
            new KitchenTicketPresenter,
            $this->events,
            $this->transaction,
        );

        foreach ($ticket->lines() as $line) {
            $useCase->execute(new MarkTicketLineDoneDto(
                orderId: $orderId,
                lineId: $line->id(),
                employeeId: 1,
            ));
        }
    }

    private function pack(int $orderId): void
    {
        $ticket = $this->tickets->findByOrderId($orderId);
        $this->assertNotNull($ticket);

        $useCase = new MarkTicketLinePackedUseCase(
            $this->tickets,
            $this->productionOrders(),
            new KitchenTicketPresenter,
            $this->events,
            $this->transaction,
        );

        foreach ($ticket->lines() as $line) {
            $useCase->execute(new MarkTicketLinePackedDto(
                orderId: $orderId,
                lineId: $line->id(),
                employeeId: 1,
            ));
        }
    }

    /**
     * @param  list<int>  $orderIds
     */
    private function releaseRoute(array $orderIds): int
    {
        $result = (new CreateRouteSheetUseCase(
            $this->sheets,
            new InMemoryDeliveryRouteOrderEligibilityPort($this->orders),
            new RouteSheetPresenter(new InMemoryDeliveryOrderStopSnapshotPort($this->orders)),
            $this->events,
            $this->transaction,
        ))->execute(new CreateRouteSheetDto($orderIds));

        return (int) $result['id'];
    }

    private function take(int $sheetId, int $courierId): void
    {
        (new TakeRouteSheetUseCase(
            $this->sheets,
            new InMemoryDeliveryCourierLookupPort($this->employees),
            new RouteSheetPresenter(new InMemoryDeliveryOrderStopSnapshotPort($this->orders)),
            $this->events,
            $this->transaction,
        ))->execute(new TakeRouteSheetDto($sheetId, $courierId));
    }

    private function settle(int $sheetId, int $orderId, string $outcome, ?string $reason = null): void
    {
        (new SettleStopUseCase(
            $this->sheets,
            new RouteSheetPresenter(new InMemoryDeliveryOrderStopSnapshotPort($this->orders)),
            $this->events,
            $this->transaction,
        ))->execute(new SettleStopDto(
            sheetId: $sheetId,
            orderId: $orderId,
            outcome: $outcome,
            reason: $reason,
            paymentMethod: $outcome === RouteStopSettled::OUTCOME_DELIVERED ? 'cash' : null,
            authorEmployeeId: 7,
        ));
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
        (new CreateKitchenTicketOnOrderConfirmed($this->tickets))->handle($confirmed);
    }

    private function orderPresenter(): OrderPresenter
    {
        return new OrderPresenter(new InMemoryOrderPaymentSnapshotPort(new InMemoryPaymentRepository));
    }

    private function react(object $event): void
    {
        if ($event instanceof OrderConfirmed) {
            (new CreateKitchenTicketOnOrderConfirmed($this->tickets))->handle($event);
        }

        if ($event instanceof KitchenTicketStarted) {
            (new MarkOrderPreparingOnKitchenTicketStarted($this->orders))->handle($event);
        }

        if ($event instanceof KitchenTicketCompleted) {
            (new MarkOrderReadyOnKitchenTicketCompleted($this->orders))->handle($event);
        }

        if ($event instanceof KitchenTicketLinePacked) {
            (new MarkOrderPackedOnKitchenTicketFullyPacked($this->orders))->handle($event);
        }

        if ($event instanceof OrderAddedToRoute) {
            (new MarkOrderAssignedToRouteOnOrderAddedToRoute($this->orders))->handle($event);
        }

        if ($event instanceof RouteSheetTaken) {
            (new MarkOrdersInTransitOnRouteSheetTaken($this->orders))->handle($event);
        }

        if ($event instanceof RouteStopSettled) {
            (new SettleOrderOnRouteStopSettled($this->orders, new InMemoryOrderAuditRepository))->handle($event);
        }

        if ($event instanceof OrderCancelled) {
            (new CancelKitchenTicketOnOrderCancelled($this->tickets))->handle($event);
            (new CancelRouteStopOnOrderCancelled($this->sheets))->handle($event);
        }
    }
}
