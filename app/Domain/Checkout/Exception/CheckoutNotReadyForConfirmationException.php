<?php

namespace App\Domain\Checkout\Exception;

use RuntimeException;

final class CheckoutNotReadyForConfirmationException extends RuntimeException
{
    public static function missingBlocks(array $missingBlocks): self
    {
        return new self(
            sprintf(
                'Чекаут не готов к подтверждению: не заполнены блоки %s.',
                implode(', ', $missingBlocks),
            ),
        );
    }
}
