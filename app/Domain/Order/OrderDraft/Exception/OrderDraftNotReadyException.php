<?php

namespace App\Domain\Order\OrderDraft\Exception;

use RuntimeException;

final class OrderDraftNotReadyException extends RuntimeException
{
    /**
     * @param  list<string>  $missingBlocks
     */
    public static function missingBlocks(array $missingBlocks): self
    {
        return new self(
            sprintf(
                'Черновик заказа не готов: не заполнены блоки %s.',
                implode(', ', $missingBlocks),
            ),
        );
    }
}
