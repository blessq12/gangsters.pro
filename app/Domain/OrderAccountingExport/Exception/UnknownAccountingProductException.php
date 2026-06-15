<?php

namespace App\Domain\OrderAccountingExport\Exception;

use RuntimeException;

final class UnknownAccountingProductException extends RuntimeException
{
    public function __construct(string $systemCode, int $productId)
    {
        parent::__construct(sprintf(
            'Товар #%d не привязан к системе учёта «%s».',
            $productId,
            $systemCode,
        ));
    }
}
