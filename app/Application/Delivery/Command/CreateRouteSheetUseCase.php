<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\CreateRouteSheetDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;

final class CreateRouteSheetUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly OrderRepository $orders,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateRouteSheetDto $dto): array
    {
        $orderIds = [];
        foreach ($dto->orderIds as $rawId) {
            $orderId = (int) $rawId;
            if ($orderId < 1) {
                throw new \InvalidArgumentException('Некорректный order_id.');
            }
            $orderIds[] = $orderId;
        }

        $orderIds = array_values(array_unique($orderIds));
        if ($orderIds === []) {
            throw new \InvalidArgumentException('Нужен хотя бы один заказ.');
        }

        foreach ($orderIds as $orderId) {
            $order = $this->orders->findById($orderId);
            if ($order === null) {
                throw new \InvalidArgumentException(sprintf('Заказ #%d не найден.', $orderId));
            }
            if ($order->status() !== OrderStatus::Ready) {
                throw new \InvalidArgumentException(
                    sprintf('Заказ #%d должен быть в статусе ready.', $orderId),
                );
            }
            if ($this->sheets->findOpenByOrderId($orderId) !== null) {
                throw new \InvalidArgumentException(
                    sprintf('Заказ #%d уже в открытом маршрутном листе.', $orderId),
                );
            }
        }

        $sheet = RouteSheet::release($orderIds);
        $this->sheets->save($sheet);

        return $this->presenter->present($sheet);
    }
}
