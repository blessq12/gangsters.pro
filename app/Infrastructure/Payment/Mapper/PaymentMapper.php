<?php

namespace App\Infrastructure\Payment\Mapper;

use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Enum\PaymentStatus;
use App\Infrastructure\Payment\Model\PAY_Payment;
use App\Shared\Enum\PaymentMethod;
use DateTimeImmutable;

final class PaymentMapper
{
    public function toDomain(PAY_Payment $row): Payment
    {
        return Payment::restore(
            id: (int) $row->id,
            orderId: (int) $row->order_id,
            method: PaymentMethod::from((string) $row->method),
            changeFromRubles: $row->change_from_rubles !== null ? (int) $row->change_from_rubles : null,
            amountKopecks: (int) $row->amount_kopecks,
            status: PaymentStatus::from((string) $row->status),
            paidAt: $row->paid_at !== null
                ? DateTimeImmutable::createFromInterface($row->paid_at)
                : null,
            refundedAt: $row->refunded_at !== null
                ? DateTimeImmutable::createFromInterface($row->refunded_at)
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(Payment $payment): array
    {
        return [
            'order_id' => $payment->orderId(),
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
            'amount_kopecks' => $payment->amountKopecks(),
            'status' => $payment->status()->value,
            'paid_at' => $payment->paidAt()?->format('Y-m-d H:i:s'),
            'refunded_at' => $payment->refundedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
