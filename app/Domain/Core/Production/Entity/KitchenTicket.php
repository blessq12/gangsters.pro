<?php

namespace App\Domain\Core\Production\Entity;

use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Event\KitchenTicketCancelled;
use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Core\Production\Event\KitchenTicketStarted;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use DateTimeImmutable;

/**
 * Кухонный тикет: единица работы филиала по заказу со снимком позиций.
 * Связь с Order — только orderId.
 * assigneeEmployeeId — повар, взявший тикет в работу (null пока queued).
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
        private readonly int $branchId,
        private readonly array $lines,
        private KitchenTicketStatus $status,
        private ?int $assigneeEmployeeId,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
    ) {
        self::assertLines($this->lines);
    }

    /**
     * @param  list<KitchenTicketLine>  $lines
     */
    public static function queue(int $orderId, int $branchId, array $lines): self
    {
        if ($orderId < 1) {
            throw new \InvalidArgumentException('orderId должен быть положительным.');
        }

        if ($branchId < 1) {
            throw new \InvalidArgumentException('branchId должен быть положительным.');
        }

        return new self(
            id: null,
            orderId: $orderId,
            branchId: $branchId,
            lines: array_values($lines),
            status: KitchenTicketStatus::Queued,
            assigneeEmployeeId: null,
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
        int $branchId,
        array $lines,
        KitchenTicketStatus $status,
        ?int $assigneeEmployeeId,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
    ): self {
        return new self(
            id: $id,
            orderId: $orderId,
            branchId: $branchId,
            lines: array_values($lines),
            status: $status,
            assigneeEmployeeId: $assigneeEmployeeId,
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

    public function branchId(): int
    {
        return $this->branchId;
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

    public function assigneeEmployeeId(): ?int
    {
        return $this->assigneeEmployeeId;
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
     * Взять тикет в работу: queued → started + assignee.
     *
     * @param  int|null  $orderBranchId  филиал, назначенный заказу на момент старта
     */
    public function start(int $employeeId, ?int $orderBranchId, ?DateTimeImmutable $at = null): void
    {
        if ($employeeId < 1) {
            throw new \InvalidArgumentException('employeeId должен быть положительным.');
        }

        if ($this->status !== KitchenTicketStatus::Queued) {
            throw new \LogicException('Начать можно только тикет в очереди.');
        }

        if ($orderBranchId !== $this->branchId) {
            throw new \LogicException('Филиал тикета не совпадает с филиалом заказа — старт запрещён.');
        }

        $this->status = KitchenTicketStatus::Started;
        $this->assigneeEmployeeId = $employeeId;
        $this->startedAt = $at ?? new DateTimeImmutable();

        if ($this->id !== null) {
            $this->record(new KitchenTicketStarted(
                orderId: $this->orderId,
                ticketId: $this->id,
                startedAt: $this->startedAt,
            ));
        }
    }

    public function complete(int $employeeId, ?DateTimeImmutable $at = null): void
    {
        if ($employeeId < 1) {
            throw new \InvalidArgumentException('employeeId должен быть положительным.');
        }

        if ($this->status !== KitchenTicketStatus::Started) {
            throw new \LogicException('Завершить можно только тикет в работе.');
        }

        if ($this->assigneeEmployeeId !== $employeeId) {
            throw new \LogicException('Тикет закреплён за другим поваром.');
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
