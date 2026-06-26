<?php

namespace App\Domain\OrderAccountingExport\Exception;

use RuntimeException;

final class UnknownAccountingProductException extends RuntimeException
{
    public function __construct(string $systemCode, int $productId, ?string $message = null)
    {
        parent::__construct($message ?? sprintf(
            'Товар #%d не привязан к системе учёта «%s».',
            $productId,
            $systemCode,
        ));
    }

    public static function missingCatalogSku(string $systemCode, int $productId): self
    {
        return new self(
            $systemCode,
            $productId,
            sprintf(
                'Товар #%d не имеет SKU каталога для экспорта в «%s».',
                $productId,
                $systemCode,
            ),
        );
    }
}
