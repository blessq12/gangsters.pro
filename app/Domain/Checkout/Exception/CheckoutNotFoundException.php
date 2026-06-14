<?php

namespace App\Domain\Checkout\Exception;

use RuntimeException;

final class CheckoutNotFoundException extends RuntimeException
{
    public static function forId(string $checkoutId): self
    {
        return new self(sprintf('Чекаут %s не найден.', $checkoutId));
    }
}
