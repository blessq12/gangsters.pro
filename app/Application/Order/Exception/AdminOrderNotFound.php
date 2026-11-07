<?php

namespace App\Application\Order\Exception;

final class AdminOrderNotFound extends \RuntimeException
{
    public static function forId(int $orderId): self
    {
        return new self('Заказ не найден: '.$orderId);
    }
}
