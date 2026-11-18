<?php

namespace App\Application\Payment\Presenter;

use App\Domain\Payment\Entity\Payment;

final class PaymentPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Payment $payment): array
    {
        return [
            'id' => $payment->hasId() ? $payment->id() : null,
            'order_id' => $payment->orderId(),
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
            'amount_kopecks' => $payment->amountKopecks(),
            'status' => $payment->status()->value,
            'paid_at' => $payment->paidAt()?->format(DATE_ATOM),
            'refunded_at' => $payment->refundedAt()?->format(DATE_ATOM),
        ];
    }
}
