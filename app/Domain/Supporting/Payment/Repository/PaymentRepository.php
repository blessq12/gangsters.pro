<?php

namespace App\Domain\Supporting\Payment\Repository;

use App\Domain\Supporting\Payment\Entity\Payment;

interface PaymentRepository
{
    public function findById(int $id): ?Payment;

    public function findByOrderId(int $orderId): ?Payment;

    /** Сохранить новый платёж (назначает id). */
    public function add(Payment $payment): void;

    public function save(Payment $payment): void;
}
