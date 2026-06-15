<?php

namespace App\Domain\Order\Exception;

use RuntimeException;

final class OrderInvariantViolation extends RuntimeException
{
    public static function invalidCheckoutReference(): self
    {
        return new self('Заказ нельзя создать без ссылки на чекаут.');
    }

    public static function emptyCart(): self
    {
        return new self('Заказ нельзя создать с пустой корзиной.');
    }

    public static function invalidAggregatorReference(): self
    {
        return new self('Заказ агрегатора нельзя создать без partner и external_order_id.');
    }
}
