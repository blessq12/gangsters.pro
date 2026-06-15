<?php

namespace App\Domain\AggregatorIngress\Exception;

use RuntimeException;

final class IngressAuthenticationFailedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Неверный API-ключ агрегатора.');
    }
}
