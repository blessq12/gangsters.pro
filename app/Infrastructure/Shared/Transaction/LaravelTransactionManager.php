<?php

namespace App\Infrastructure\Shared\Transaction;

use App\Shared\Transaction\TransactionManager;
use Illuminate\Support\Facades\DB;

final class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $operation): mixed
    {
        return DB::transaction(static fn () => $operation());
    }
}
