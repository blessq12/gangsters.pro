<?php

namespace App\Application\Order\Command;

use App\Application\Order\Contracts\MarkOrderPaidContract;
use App\Application\Order\Events\OrderUpdatedIntegrationEvent;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Shared\Events\DomainEventBus;
use App\Shared\Events\IntegrationEventBus;

final class MarkOrderPaidService implements MarkOrderPaidContract
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderPresenter $presenter,
        private readonly DomainEventBus $domainEvents,
        private readonly IntegrationEventBus $integrationEvents,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(string $orderId): array
    {
        $order = $this->orders->getById($orderId);
        $currentPayment = $order->getPaymentInfo();

        $order->setPaymentInfo(new PaymentInfo(
            method: $currentPayment?->method ?? PaymentMethod::Cash->value,
            status: PaymentStatus::Paid->value,
        ));

        $this->orders->save($order);
        $this->domainEvents->publish(new OrderPaid($order));
        $this->integrationEvents->publish(OrderUpdatedIntegrationEvent::fromOrder($order));

        return $this->presenter->present($order);
    }
}
