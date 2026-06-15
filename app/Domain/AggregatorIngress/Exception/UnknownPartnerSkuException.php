<?php

namespace App\Domain\AggregatorIngress\Exception;

use RuntimeException;

final class UnknownPartnerSkuException extends RuntimeException
{
    public function __construct(string $partnerSku)
    {
        parent::__construct(sprintf('Неизвестный SKU партнёра: %s.', $partnerSku));
    }
}
