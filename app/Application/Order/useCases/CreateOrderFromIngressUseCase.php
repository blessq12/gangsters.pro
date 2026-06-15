<?php

namespace App\Application\Order\useCases;

use App\Application\Order\DTO\CreateOrderFromIngressDto;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\ValueObject\OrderAggregatorReference;
use Illuminate\Support\Facades\Event;

/**
 * Сценарий: создать заказ из нормализованного ingress-обращения агрегатора.
 */
final class CreateOrderFromIngressUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(CreateOrderFromIngressDto $input): Order
    {
        $existing = $this->orders->findByPartnerAndExternalOrderId(
            $input->partnerCode,
            $input->externalOrderId,
        );

        if ($existing instanceof Order) {
            return $existing;
        }

        $order = Order::fromIngressSnapshot(
            aggregatorReference: new OrderAggregatorReference(
                partnerCode: $input->partnerCode,
                externalOrderId: $input->externalOrderId,
            ),
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
