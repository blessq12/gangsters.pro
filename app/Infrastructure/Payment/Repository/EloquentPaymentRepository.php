<?php

namespace App\Infrastructure\Payment\Repository;

use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Repository\PaymentRepository;
use App\Infrastructure\Payment\Mapper\PaymentMapper;
use App\Infrastructure\Payment\Model\PAY_Payment;

final class EloquentPaymentRepository implements PaymentRepository
{
    public function __construct(
        private readonly PaymentMapper $mapper,
    ) {}

    public function findById(int $id): ?Payment
    {
        $row = PAY_Payment::query()->find($id);

        return $row instanceof PAY_Payment ? $this->mapper->toDomain($row) : null;
    }

    public function findByOrderId(int $orderId): ?Payment
    {
        $row = PAY_Payment::query()
            ->where('order_id', $orderId)
            ->first();

        return $row instanceof PAY_Payment ? $this->mapper->toDomain($row) : null;
    }

    public function add(Payment $payment): void
    {
        if ($payment->hasId()) {
            throw new \InvalidArgumentException('Платёж уже сохранён — используй save.');
        }

        $row = PAY_Payment::query()->create($this->mapper->toPersistence($payment));
        $payment->assignId((int) $row->id);
    }

    public function save(Payment $payment): void
    {
        if (! $payment->hasId()) {
            throw new \InvalidArgumentException('Платёж ещё не создан — используй add.');
        }

        PAY_Payment::query()
            ->whereKey($payment->id())
            ->update($this->mapper->toPersistence($payment));
    }
}
