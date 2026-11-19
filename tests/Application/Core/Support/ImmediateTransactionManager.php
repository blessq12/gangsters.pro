<?php

namespace Tests\Application\Core\Support;

use App\Shared\Transaction\TransactionManager;

final class ImmediateTransactionManager implements TransactionManager
{
    public function run(callable $operation): mixed
    {
        return $operation();
    }
}
