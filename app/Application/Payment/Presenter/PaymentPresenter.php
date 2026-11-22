<?php

namespace App\Application\Payment\Presenter;

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
            'order_number' => $this->formatOrderNumber($payment->orderId(), $createdAt),
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
            'amount_kopecks' => $payment->amountKopecks(),
            'status' => $payment->status()->value,
            'paid_at' => $payment->paidAt()?->format(DATE_ATOM),
            'refunded_at' => $payment->refundedAt()?->format(DATE_ATOM),
            'created_at' => $payment->createdAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * Локальный display-формат номера заказа (без импорта Order VO).
     */
    private function formatOrderNumber(int $orderId, \DateTimeInterface $at): string
    {
        if ($orderId < 1) {
            return '';
        }

        return sprintf(
            'ORD-%s-%02d',
            str_pad((string) $orderId, 4, '0', STR_PAD_LEFT),
            (int) $at->format('y'),
        );
    }
}
