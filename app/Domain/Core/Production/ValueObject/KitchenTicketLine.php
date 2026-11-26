<?php

namespace App\Domain\Core\Production\ValueObject;

use App\Domain\Core\Production\Enum\KitchenTicketLineStatus;
use DateTimeImmutable;

/**
 * Снимок позиции для кухни + статусы готовности/сборки.
 */
final class KitchenTicketLine
{
    private function __construct(
        private ?int $id,
        private readonly int $productId,
        private readonly string $name,
        private readonly int $quantity,
        private KitchenTicketLineStatus $status,
        private ?int $doneBy,
        private ?DateTimeImmutable $doneAt,
        private ?int $packedBy,
        private ?DateTimeImmutable $packedAt,
    ) {
        if ($this->productId < 1) {
            throw new \InvalidArgumentException('productId должен быть положительным.');
        }

        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Название позиции тикета обязательно.');
        }

        if ($this->quantity < 1) {
            throw new \InvalidArgumentException('Количество в тикете должно быть не меньше 1.');
        }
    }

    public static function snapshot(int $productId, string $name, int $quantity): self
    {
        return new self(
            id: null,
            productId: $productId,
            name: $name,
            quantity: $quantity,
            status: KitchenTicketLineStatus::Queued,
            doneBy: null,
            doneAt: null,
            packedBy: null,
            packedAt: null,
        );
    }

    public static function restore(
        ?int $id,
        int $productId,
        string $name,
        int $quantity,
        KitchenTicketLineStatus $status,
        ?int $doneBy,
        ?DateTimeImmutable $doneAt,
        ?int $packedBy,
        ?DateTimeImmutable $packedAt,
    ): self {
        return new self(
            id: $id,
            productId: $productId,
            name: $name,
            quantity: $quantity,
            status: $status,
            doneBy: $doneBy,
            doneAt: $doneAt,
            packedBy: $packedBy,
            packedAt: $packedAt,
        );
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id линии уже назначен.');
        }

        if ($id < 1) {
            throw new \InvalidArgumentException('id линии должен быть положительным.');
        }

        $this->id = $id;
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Линия тикета ещё не сохранена.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function status(): KitchenTicketLineStatus
    {
        return $this->status;
    }

    public function doneBy(): ?int
    {
        return $this->doneBy;
    }

    public function doneAt(): ?DateTimeImmutable
    {
        return $this->doneAt;
    }

    public function packedBy(): ?int
    {
        return $this->packedBy;
    }

    public function packedAt(): ?DateTimeImmutable
    {
        return $this->packedAt;
    }

    public function isPacked(): bool
    {
        return $this->packedBy !== null && $this->packedAt !== null;
    }

    public function markDone(int $employeeId, ?DateTimeImmutable $at = null): void
    {
        if ($employeeId < 1) {
            throw new \InvalidArgumentException('employeeId должен быть положительным.');
        }

        if ($this->status === KitchenTicketLineStatus::Cancelled) {
            throw new \LogicException('Отменённую позицию нельзя приготовить.');
        }

        if ($this->status === KitchenTicketLineStatus::Done) {
            throw new \LogicException('Позиция уже приготовлена.');
        }

        $this->status = KitchenTicketLineStatus::Done;
        $this->doneBy = $employeeId;
        $this->doneAt = $at ?? new DateTimeImmutable;
    }

    public function markPacked(int $employeeId, ?DateTimeImmutable $at = null): void
    {
        if ($employeeId < 1) {
            throw new \InvalidArgumentException('employeeId должен быть положительным.');
        }

        if ($this->status !== KitchenTicketLineStatus::Done) {
            throw new \LogicException('Собрать можно только приготовленную позицию.');
        }

        if ($this->isPacked()) {
            throw new \LogicException('Позиция уже собрана.');
        }

        $this->packedBy = $employeeId;
        $this->packedAt = $at ?? new DateTimeImmutable;
    }

    public function markCancelled(): void
    {
        if ($this->status === KitchenTicketLineStatus::Cancelled) {
            return;
        }

        $this->status = KitchenTicketLineStatus::Cancelled;
        $this->doneBy = null;
        $this->doneAt = null;
        $this->packedBy = null;
        $this->packedAt = null;
    }
}
