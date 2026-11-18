<?php

namespace App\Domain\Warehouse\Entity;

use DateTimeImmutable;

/** Списание ингредиента со склада (в т.ч. по завершённому кухонному тикету). */
final class StockWriteOff
{
    private function __construct(
        private ?int $id,
        private readonly int $ingredientId,
        private readonly float $quantity,
        private readonly string $reason,
        private readonly ?int $kitchenTicketId,
        private readonly DateTimeImmutable $occurredAt,
    ) {}

    public static function record(
        int $ingredientId,
        float $quantity,
        string $reason,
        ?int $kitchenTicketId,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        if ($ingredientId < 1) {
            throw new \InvalidArgumentException('ingredientId должен быть положительным.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Количество списания должно быть положительным.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('Причина списания обязательна.');
        }

        return new self(
            id: null,
            ingredientId: $ingredientId,
            quantity: $quantity,
            reason: $reason,
            kitchenTicketId: $kitchenTicketId,
            occurredAt: $occurredAt ?? new DateTimeImmutable(),
        );
    }

    public static function restore(
        int $id,
        int $ingredientId,
        float $quantity,
        string $reason,
        ?int $kitchenTicketId,
        DateTimeImmutable $occurredAt,
    ): self {
        return new self(
            id: $id,
            ingredientId: $ingredientId,
            quantity: $quantity,
            reason: $reason,
            kitchenTicketId: $kitchenTicketId,
            occurredAt: $occurredAt,
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
            throw new \LogicException('Списание ещё не сохранено.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function ingredientId(): int
    {
        return $this->ingredientId;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function kitchenTicketId(): ?int
    {
        return $this->kitchenTicketId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
