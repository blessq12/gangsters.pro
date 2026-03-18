<?php

namespace App\Application\YandexFood\Presenter;

use App\Application\Order\DTO\OrderResponseDTO;

class YandexOrderPresenter
{
    public function present(OrderResponseDTO $order): array
    {
        return [
            'result' => 'OK',
            'order' => [
                'id' => (string) $order->id,
                'items' => $order->items,
                'status' => $order->status,
            ],
        ];
    }
}

