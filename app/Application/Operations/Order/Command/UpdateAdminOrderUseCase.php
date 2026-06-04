<?php

namespace App\Application\Operations\Order\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\DTO\UpdateAdminOrderDto;
use App\Application\Operations\Order\Query\GetAdminOrderDetailQuery;
use App\Application\Order\Contracts\OrderExternalLifecycleContract;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;

final class UpdateAdminOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderExternalLifecycleContract $orderLifecycle,
        private readonly GetAdminOrderDetailQuery $orderDetail,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateAdminOrderDto $dto): array
    {
        $order = $this->orders->getById($dto->orderId);

        if ($order->getStatus()->value === OrderStatus::delivered()->value) {
            throw new ApiException('Нельзя изменить состав доставленного заказа.', 422);
        }

        if ($dto->items === []) {
            throw new ApiException('Заказ должен содержать хотя бы одну позицию.', 422);
        }

        foreach ($dto->items as $row) {
            if ((int) ($row['quantity'] ?? 0) < 1) {
                throw new ApiException('Количество должно быть не меньше 1.', 422);
            }
        }

        $updated = $this->orderLifecycle->updateOrderItems($dto->orderId, $dto->items);
        if ($updated === null) {
            throw new ApiException('Order not found.', 404);
        }

        return $this->orderDetail->execute($dto->orderId);
    }
}
