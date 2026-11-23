<?php

namespace App\Shared\Transaction;

/**
 * Единица работы: всё, что выполнено внутри run(), фиксируется целиком или откатывается целиком.
 * Синхронные слушатели доменных событий, вызванные внутри, попадают в ту же транзакцию.
 */
interface TransactionManager
{
    /**
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    public function run(callable $operation): mixed;
}
