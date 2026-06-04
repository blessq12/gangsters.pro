<?php

namespace Tests\Unit\Application\Operations\Order;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Command\UpdateAdminOrderUseCase;
use App\Application\Operations\Order\Contracts\AdminOrderReadRepository;
use App\Application\Operations\Order\DTO\UpdateAdminOrderDto;
use App\Application\Operations\Order\Presenter\AdminOrderPresenter;
use App\Application\Operations\Order\Query\GetAdminOrderDetailQuery;
use App\Application\Order\Contracts\OrderExternalLifecycleContract;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use PHPUnit\Framework\TestCase;

final class UpdateAdminOrderUseCaseTest extends TestCase
{
    public function test_execute_rejects_delivered_order(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('getStatus')->willReturn(OrderStatus::delivered());

        $repo = $this->createMock(OrderRepositoryInterface::class);
        $repo->method('getById')->willReturn($order);

        $lifecycle = $this->createMock(OrderExternalLifecycleContract::class);
        $lifecycle->expects($this->never())->method('updateOrderItems');

        $useCase = new UpdateAdminOrderUseCase(
            $repo,
            $lifecycle,
            new GetAdminOrderDetailQuery(
                $this->createMock(AdminOrderReadRepository::class),
                new AdminOrderPresenter(new OrderPresenter),
            ),
        );

        $this->expectException(ApiException::class);

        $useCase->execute(new UpdateAdminOrderDto(
            orderId: 'order-1',
            items: [['product_id' => 1, 'quantity' => 2]],
        ));
    }
}
