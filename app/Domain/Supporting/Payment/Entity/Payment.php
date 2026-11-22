<?php

namespace App\Domain\Supporting\Payment\Entity;

use App\Domain\Supporting\Payment\Enum\PaymentStatus;
use App\Domain\Supporting\Payment\Event\PaymentCancelled;
use App\Domain\Supporting\Payment\Event\PaymentInitiated;
use App\Domain\Supporting\Payment\Event\PaymentPaid;
use App\Domain\Supporting\Payment\Event\PaymentRefunded;
use App\Shared\Enum\PaymentMethod;
use DateTimeImmutable;

/**
 * Платёж по заказу: ожидает → оплачен → возврат. Заказ знает только его id.
 */
final class Payment
{
    /** @var list<object> */
    private array $domainEvents = [];

    private function __construct(
        private ?int $id,
        private readonly int $orderId,
        private readonly PaymentMethod $method,
        private readonly ?int $changeFromRubles,
        private readonly int $amountKopecks,
        private PaymentStatus $status,
        private ?DateTimeImmutable $paidAt,
        private ?DateTimeImmutable $refundedAt,
        private readonly ?DateTimeImmutable $createdAt,
    ) {}

    public static function initiate(
        int $orderId,
        PaymentMethod $method,
        ?int $changeFromRubles,
        int $amountKopecks,
    ): self {
        if ($orderId < 1) {
            throw new \InvalidArgumentException('orderId должен быть положительным.');
        }

        if ($amountKopecks < 0) {
            throw new \InvalidArgumentException('Сумма платежа не может быть отрицательной.');
        }

        if ($changeFromRubles !== null && $changeFromRubles < 0) {
            throw new \InvalidArgumentException('Сумма для сдачи не может быть отрицательной.');
        }

        return new self(
            id: null,
            orderId: $orderId,
            method: $method,
            changeFromRubles: $changeFromRubles,
            amountKopecks: $amountKopecks,
            status: PaymentStatus::Pending,
            paidAt: null,
            refundedAt: null,
            createdAt: null,
        );
    }

    public static function restore(
        int $id,
        int $orderId,
        PaymentMethod $method,
        ?int $changeFromRubles,
        int $amountKopecks,
        PaymentStatus $status,
        ?DateTimeImmutable $paidAt,
        ?DateTimeImmutable $refundedAt,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            id: $id,
            orderId: $orderId,
            method: $method,
            changeFromRubles: $changeFromRubles,
            amountKopecks: $amountKopecks,
            status: $status,
            paidAt: $paidAt,
            refundedAt: $refundedAt,
            createdAt: $createdAt,
        );
    }

    /**
     * Факт «платёж заведён» возникает, когда платёж получает идентичность.
     */
    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        if ($id < 1) {
            throw new \InvalidArgumentException('id должен быть положительным.');
        }

        $this->id = $id;
        $this->record(new PaymentInitiated(paymentId: $id, orderId: $this->orderId));
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Платёж ещё не сохранён.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function method(): PaymentMethod
    {
        return $this->method;
    }

    public function changeFromRubles(): ?int
    {
        return $this->changeFromRubles;
    }

    public function amountKopecks(): int
    {
        return $this->amountKopecks;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }

    public function paidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function refundedAt(): ?DateTimeImmutable
    {
        return $this->refundedAt;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function markPaid(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== PaymentStatus::Pending) {
            throw new \LogicException('Оплаченным можно отметить только ожидающий платёж.');
        }

        $this->status = PaymentStatus::Paid;
        $this->paidAt = $at ?? new DateTimeImmutable();
        $this->record(new PaymentPaid(
            paymentId: $this->id(),
            orderId: $this->orderId,
            paidAt: $this->paidAt,
        ));
    }

    public function refund(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== PaymentStatus::Paid) {
            throw new \LogicException('Вернуть можно только оплаченный платёж.');
        }

        $this->status = PaymentStatus::Refunded;
        $this->refundedAt = $at ?? new DateTimeImmutable();
        $this->record(new PaymentRefunded(
            paymentId: $this->id(),
            orderId: $this->orderId,
            refundedAt: $this->refundedAt,
        ));
    }

    /**
     * Отмена ожидающего поручения (заказ отменён до оплаты).
     */
    public function cancel(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== PaymentStatus::Pending) {
            throw new \LogicException('Отменить можно только ожидающий платёж.');
        }

        $cancelledAt = $at ?? new DateTimeImmutable();
        $this->status = PaymentStatus::Cancelled;
        $this->record(new PaymentCancelled(
            paymentId: $this->id(),
            orderId: $this->orderId,
            cancelledAt: $cancelledAt,
        ));
    }

    /**
     * @return list<object>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function record(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
