<?php

namespace App\Application\YandexFood\Presenter;

use App\Domain\Order\ValueObjects\OrderStatus;

class YandexStatusPresenter
{
    public function present(OrderStatus $status, \DateTimeInterface $updatedAt): array
    {
        return [
            'status' => (string) $status->value,
            'comment' => '',
            'updatedAt' => $updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}

