<?php

namespace App\Domain\Order\Exception;

use RuntimeException;

final class OrderRepeatNotSupportedException extends RuntimeException
{
    public static function forNonSiteOrder(): self
    {
        return new self('Повтор доступен только для заказов с сайта.');
    }
}
