<?php

namespace App\Domain\Production\Entity;

use App\Domain\Production\Enum\KitchenTicketStatus;
use DateTimeImmutable;

/** Кухонный тикет: единица работы кухни по заказу (очередь → в работе → готово / отменено). */
final class KitchenTicket
{
    private function __construct(
        private ?int $id,
        private readonly int $orderId,
        private KitchenTicketStatus $status,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
    ) {}

    public static function queue(int $orderId): self
    {
        if ($orderId < 1) {
            throw new \InvalidArgumentException('orderId должен быть положительным.');
        }

        return new self(
            id: null,
            orderId: $orderId,
            status: KitchenTicketStatus::Queued,
            startedAt: null,
            completedAt: null,
        );
    }

    public static function restore(
        int $id,
        int $orderId,
        KitchenTicketStatus $status,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
    ): self {
        return new self(
            id: $id,
            orderId: $orderId,
            status: $status,
            startedAt: $startedAt,
            completedAt: $completedAt,
        );
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        if ($id < 1) {
            throw new \InvalidArgumentException('id должен быть положительным.');
        }

        $this->id = $id;
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Тикет ещё не сохранён.');
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

    public function status(): KitchenTicketStatus
    {
        return $this->status;
    }

    public function startedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function start(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== KitchenTicketStatus::Queued) {
            throw new \LogicException('Начать можно только тикет в очереди.');
        }

        $this->status = KitchenTicketStatus::Started;
        $this->startedAt = $at ?? new DateTimeImmutable();
    }

    public function complete(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== KitchenTicketStatus::Started) {
            throw new \LogicException('Завершить можно только тикет в работе.');
        }

        $this->status = KitchenTicketStatus::Completed;
        $this->completedAt = $at ?? new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status === KitchenTicketStatus::Completed || $this->status === KitchenTicketStatus::Cancelled) {
            throw new \LogicException('Тикет уже завершён или отменён.');
        }

        $this->status = KitchenTicketStatus::Cancelled;
    }
}
