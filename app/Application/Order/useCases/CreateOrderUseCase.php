<?php

namespace App\Application\Order\useCases;

use App\Application\Order\DTO\CreateOrderDto;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Repository\OrderRepository;
use Illuminate\Support\Facades\Event;

/**
 * Сценарий: создать заказ из снимка подтверждённого чекаута.
 */
final class CreateOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(CreateOrderDto $input): Order
    {
        $existing = $this->orders->findByClientRequestId($input->clientRequestId);

        if ($existing instanceof Order) {
            return $existing;
        }

        $order = Order::fromCheckoutSnapshot(
            clientRequestId: $input->clientRequestId,
            cart: $input->cart,
            client: $input->client,
            delivery: $input->delivery,
            payment: $input->payment,
            createdAt: $input->createdAt,
        );

        $this->orders->save($order);

        Event::dispatch(OrderCreated::fromOrder($order));

        return $order;
    }
}
