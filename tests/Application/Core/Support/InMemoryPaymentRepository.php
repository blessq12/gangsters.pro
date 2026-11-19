<?php

namespace Tests\Application\Core\Support;

use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;

final class InMemoryPaymentRepository implements PaymentRepository
{
    public function findById(int $id): ?Payment
    {
        return null;
    }

    public function findByOrderId(int $orderId): ?Payment
    {
        return null;
    }

    public function add(Payment $payment): void {}

    public function save(Payment $payment): void {}
}
