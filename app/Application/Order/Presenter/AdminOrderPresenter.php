<?php

namespace App\Application\Order\Presenter;

use App\Domain\Core\Order\Entity\Order;

final class AdminOrderPresenter
{
    public function __construct(
        private readonly OrderPresenter $orderPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $payload = $this->orderPresenter->present($order);
        $payload['source'] = $order->source();
        $payload['partner_code'] = $order->partnerCode();
        $payload['external_order_id'] = $order->externalOrderId();

        return $payload;
    }

    /**
     * @param  list<Order>  $orders
     * @return list<array<string, mixed>>
     */
    public function presentList(array $orders): array
    {
        $result = [];
        foreach ($orders as $order) {
            $result[] = $this->present($order);
        }

        return $result;
    }
}
