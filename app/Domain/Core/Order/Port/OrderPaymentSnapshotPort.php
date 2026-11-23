<?php

namespace App\Domain\Core\Order\Port;

/**
 * Снимок платежа для презентации заказа. Order Domain не импортирует Payment.
 *
 * @phpstan-type OrderPaymentSnapshot array{
 *     id: int,
 *     status: string,
 *     method: string,
 *     amount_kopecks: int,
 *     change_from_rubles: int|null
 * }
 */
interface OrderPaymentSnapshotPort
{
    /**
     * @return OrderPaymentSnapshot|null
     */
    public function findByPaymentId(int $paymentId): ?array;
}
