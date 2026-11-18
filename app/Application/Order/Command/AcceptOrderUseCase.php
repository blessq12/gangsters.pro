<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Company\Repository\KitchenRepository;
use App\Domain\Order\Repository\OrderRepository;

final class AcceptOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly KitchenRepository $kitchens,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(AcceptOrderDto $dto): array
    {
        $order = $this->orders->findById($dto->orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        $kitchenId = $dto->kitchenId;
        if ($kitchenId === null || $kitchenId < 1) {
            $fromDelivery = $order->delivery()['kitchen_id'] ?? null;
            $kitchenId = $fromDelivery !== null ? (int) $fromDelivery : null;
        }

        if ($kitchenId === null || $kitchenId < 1) {
            throw new \InvalidArgumentException('Для подтверждения нужен kitchen_id.');
        }

        $kitchen = $this->kitchens->findById($kitchenId);
        if ($kitchen === null) {
            throw new \InvalidArgumentException('Кухня не найдена.');
        }

        $order->accept();
        $order->assignKitchen($kitchen->id(), $kitchen->branchId());
        $this->orders->save($order);

        return $this->presenter->present($order);
    }
}
