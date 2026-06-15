<?php

namespace App\Domain\AggregatorIngress\Exception;

use RuntimeException;

final class PartnerNotConfiguredException extends RuntimeException
{
    public function __construct(string $partnerCode)
    {
        parent::__construct(sprintf('Партнёр ingress «%s» не настроен.', $partnerCode));
    }
}
