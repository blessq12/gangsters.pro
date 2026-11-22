<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Order\Port\OrderPaymentSnapshotPort;

final class InMemoryOrderPaymentSnapshotPort implements OrderPaymentSnapshotPort
{
    public function __construct(
        private readonly InMemoryPaymentRepository $payments,
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
