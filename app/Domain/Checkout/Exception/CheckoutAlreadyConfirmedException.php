<?php

namespace App\Domain\Checkout\Exception;

use RuntimeException;

final class CheckoutAlreadyConfirmedException extends RuntimeException
{
    public static function forId(string $checkoutId): self
    {
        return new self(sprintf('Чекаут %s уже подтверждён.', $checkoutId));
    }
}
