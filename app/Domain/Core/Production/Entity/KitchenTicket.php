<?php

namespace App\Domain\Core\Production\Entity;

use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Event\KitchenTicketCancelled;
use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Core\Production\Event\KitchenTicketStarted;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use DateTimeImmutable;

/**
 * Кухонный тикет: единица работы кухни по заказу со снимком позиций.
 * Связь с Order — только orderId.
 */
final class KitchenTicket
{
    /** @var list<object> */
    private array $domainEvents = [];

    /**
     * @param  list<KitchenTicketLine>  $lines
     */
    private function __construct(
        private ?int $id,
        private readonly int $orderId,
        private readonly int $kitchenId,
        private readonly array $lines,
        private KitchenTicketStatus $status,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
    ) {
        self::assertLines($this->lines);
    }

    /**
     * @param  list<KitchenTicketLine>  $lines
     */
    public static function queue(int $orderId, int $kitchenId, array $lines): self
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
            lines: array_values($lines),
            status: KitchenTicketStatus::Queued,
            startedAt: null,
            completedAt: null,
        );
    }

    /**
     * @param  list<KitchenTicketLine>  $lines
     */
    public static function restore(
        int $id,
        int $orderId,
        int $kitchenId,
        array $lines,
        KitchenTicketStatus $status,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
    ): self {
        return new self(
            id: $id,
            orderId: $orderId,
            kitchenId: $kitchenId,
            lines: array_values($lines),
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

    /**
     * @return list<KitchenTicketLine>
     */
    public function lines(): array
    {
        return $this->lines;
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

    /**
     * @param  list<KitchenTicketLine>  $lines
     */
    private static function assertLines(array $lines): void
    {
        if ($lines === []) {
            throw new \InvalidArgumentException('Тикет нельзя создать без позиций.');
        }

        foreach ($lines as $line) {
            if (! $line instanceof KitchenTicketLine) {
                throw new \InvalidArgumentException('Ожидался KitchenTicketLine.');
            }
        }
    }
}
