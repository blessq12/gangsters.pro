<?php

namespace App\Domain\Production\Entity;

use App\Domain\Production\Enum\KitchenTicketStatus;
use App\Domain\Production\Event\KitchenTicketCancelled;
use App\Domain\Production\Event\KitchenTicketCompleted;
use App\Domain\Production\Event\KitchenTicketStarted;
use DateTimeImmutable;

/** Кухонный тикет: единица работы кухни по заказу (очередь → в работе → готово / отменено). */
final class KitchenTicket
{
    /** @var list<object> */
    private array $domainEvents = [];

    private function __construct(
        private ?int $id,
        private readonly int $orderId,
        private readonly int $kitchenId,
        private KitchenTicketStatus $status,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
    ) {}

    public static function queue(int $orderId, int $kitchenId): self
    {
        if ($orderId < 1) {
            throw new \InvalidArgumentException('orderId должен быть положительным.');
        }

        if ($kitchenId < 1) {
            throw new \InvalidArgumentException('kitchenId должен быть положительным.');
        }

        return new self(
            id: null,
            orderId: $orderId,
            kitchenId: $kitchenId,
            status: KitchenTicketStatus::Queued,
            startedAt: null,
            completedAt: null,
        );
    }

    public static function restore(
        int $id,
        int $orderId,
        int $kitchenId,
        KitchenTicketStatus $status,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
    ): self {
        return new self(
            id: $id,
            orderId: $orderId,
            kitchenId: $kitchenId,
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

    public function kitchenId(): int
    {
        return $this->kitchenId;
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

    /**
     * @param  int|null  $orderKitchenId  кухня, назначенная заказу на момент старта
     */
    public function start(?int $orderKitchenId, ?DateTimeImmutable $at = null): void
    {
        if ($this->status !== KitchenTicketStatus::Queued) {
            throw new \LogicException('Начать можно только тикет в очереди.');
        }

        if ($orderKitchenId !== $this->kitchenId) {
            throw new \LogicException('Кухня тикета не совпадает с кухней заказа — старт запрещён.');
        }

        $this->status = KitchenTicketStatus::Started;
        $this->startedAt = $at ?? new DateTimeImmutable();

        if ($this->id !== null) {
            $this->record(new KitchenTicketStarted(
                orderId: $this->orderId,
                ticketId: $this->id,
                startedAt: $this->startedAt,
            ));
        }
    }

    public function complete(?DateTimeImmutable $at = null): void
    {
        if ($this->status !== KitchenTicketStatus::Started) {
            throw new \LogicException('Завершить можно только тикет в работе.');
        }

        $this->status = KitchenTicketStatus::Completed;
        $this->completedAt = $at ?? new DateTimeImmutable();

        if ($this->id !== null) {
            $this->record(new KitchenTicketCompleted(
                orderId: $this->orderId,
                ticketId: $this->id,
                completedAt: $this->completedAt,
            ));
        }
    }

    public function cancel(): void
    {
        if ($this->status === KitchenTicketStatus::Completed || $this->status === KitchenTicketStatus::Cancelled) {
            throw new \LogicException('Тикет уже завершён или отменён.');
        }

        $this->status = KitchenTicketStatus::Cancelled;

        if ($this->id !== null) {
            $this->record(new KitchenTicketCancelled(
                orderId: $this->orderId,
                ticketId: $this->id,
            ));
        }
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
