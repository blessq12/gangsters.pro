<?php

namespace App\Application\Payment\Presenter;

use App\Domain\Core\Order\ValueObject\OrderNumber;
use App\Domain\Supporting\Payment\Entity\Payment;

final class PaymentPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Payment $payment): array
    {
        $createdAt = $payment->createdAt() ?? new \DateTimeImmutable;

        return [
            'id' => $payment->hasId() ? $payment->id() : null,
            'order_id' => $payment->orderId(),
            'order_number' => OrderNumber::format($payment->orderId(), $createdAt),
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
            'amount_kopecks' => $payment->amountKopecks(),
            'status' => $payment->status()->value,
            'paid_at' => $payment->paidAt()?->format(DATE_ATOM),
            'refunded_at' => $payment->refundedAt()?->format(DATE_ATOM),
            'created_at' => $payment->createdAt()?->format(DATE_ATOM),
        ];
    }
}
