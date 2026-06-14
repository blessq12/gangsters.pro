<?php

namespace App\Domain\Order\Exception;

use RuntimeException;

final class OrderNotFoundException extends RuntimeException
{
    public static function forId(int $orderId): self
    {
        return new self(sprintf('Заказ %d не найден.', $orderId));
    }
}
