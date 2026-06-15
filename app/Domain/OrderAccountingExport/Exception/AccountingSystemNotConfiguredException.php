<?php

namespace App\Domain\OrderAccountingExport\Exception;

use RuntimeException;

final class AccountingSystemNotConfiguredException extends RuntimeException
{
    public function __construct(string $systemCode)
    {
        parent::__construct(sprintf('Система учёта «%s» не настроена.', $systemCode));
    }
}
