<?php

namespace App\Infrastructure\Order\Adapter;

use App\Domain\Core\Order\Port\OrderPaymentSnapshotPort;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;

/**
 * ACL: Order читает снимок платежа через PaymentRepository в Infrastructure.
 */
final class EloquentOrderPaymentSnapshotPort implements OrderPaymentSnapshotPort
{
    public function __construct(
        private readonly PaymentRepository $payments,
    ) {}

    public function findByPaymentId(int $paymentId): ?array
    {
        $payment = $this->payments->findById($paymentId);
        if ($payment === null || ! $payment->hasId()) {
            return null;
        }

        return [
            'id' => $payment->id(),
            'status' => $payment->status()->value,
            'method' => $payment->method()->value,
            'amount_kopecks' => $payment->amountKopecks(),
            'change_from_rubles' => $payment->changeFromRubles(),
        ];
    }
}
