<?php

namespace App\Application\Operations\Order\Presenter;

use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entities\Order;
use App\Support\Order\OrderStatusLabels;

final class AdminOrderPresenter
{
    public function __construct(
        private readonly OrderPresenter $orderPresenter,
    ) {
    }

    public function presentListItem(Order $order): array
    {
        $base = $this->orderPresenter->present($order);

        return [
            'id' => $base['id'],
            'client_id' => $base['client_id'],
            'status' => $base['status'],
            'status_label' => OrderStatusLabels::statusLabel((string) $base['status']),
            'customer_name' => $base['customer']['name'] ?? '',
            'customer_phone' => $base['customer']['phone'] ?? '',
            'total' => $base['total'],
            'payment_status' => $base['payment']['status'] ?? null,
            'created_at' => $base['created_at'],
        ];
    }

    public function presentDetail(Order $order): array
    {
        return $this->orderPresenter->present($order);
    }
}
