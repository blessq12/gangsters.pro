<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\ChangeOrderStatusDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Repository\OrderRepository;

/**
 * Ручная смена статуса — только безопасные исключения.
 *
 * Запрещены статусы событийного контура:
 * preparing, ready, assigned_to_route, in_transit, delivered, written_off, confirmed.
 *
 * Допустимо: cancelled (публикует OrderCancelled → Production/Delivery/Payment).
 */
final class ChangeOrderStatusUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ChangeOrderStatusDto $dto): array
    {
        $order = $this->orders->findById($dto->orderId);
        if ($order === null) {
            throw new \InvalidArgumentException('Заказ не найден.');
        }

        $target = OrderStatus::tryFrom(trim($dto->targetStatus));
        if ($target === null) {
            throw new \InvalidArgumentException('Некорректный целевой статус.');
        }

        match ($target) {
            OrderStatus::Cancelled => $order->cancel(),
            OrderStatus::Confirmed => throw new \InvalidArgumentException(
                'Подтверждение заказа — через accept с назначением кухни (OrderConfirmed).',
            ),
            OrderStatus::Preparing,
            OrderStatus::Ready,
            OrderStatus::AssignedToRoute,
            OrderStatus::InTransit,
            OrderStatus::Delivered,
            OrderStatus::WrittenOff => throw new \InvalidArgumentException(
                sprintf(
                    'Статус «%s» выставляется только через доменные события, не через change status.',
                    $target->value,
                ),
            ),
            OrderStatus::New, OrderStatus::Preorder => throw new \InvalidArgumentException(
                'Нельзя перевести заказ в статус «'.$target->value.'» через change status.',
            ),
        };

        $this->orders->save($order);

        foreach ($order->pullDomainEvents() as $event) {
            event($event);
        }

        return $this->presenter->present($order);
    }
}
