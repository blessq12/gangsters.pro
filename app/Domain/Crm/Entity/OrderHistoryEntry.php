<?php

namespace App\Domain\Crm\Entity;

use DateTimeImmutable;

/** Агрегат истории заказов: слепок созданного заказа. */
final class OrderHistoryEntry
{
    /**
     * @param array<string, mixed> $orderSnapshot
     */
    private function __construct(
        private ?int $id,
        private readonly int $clientId,
        private readonly array $orderSnapshot,
        private readonly DateTimeImmutable $placedAt,
    ) {}

    /**
     * @param array<string, mixed> $orderSnapshot
     */
    public static function record(
        int $clientId,
        array $orderSnapshot,
        DateTimeImmutable $placedAt,
    ): self {
        if ($clientId < 1) {
            throw new \InvalidArgumentException('clientId должен быть положительным.');
        }

        if ($orderSnapshot === []) {
            throw new \InvalidArgumentException('Слепок заказа обязателен.');
        }

        return new self(
            id: null,
            clientId: $clientId,
            orderSnapshot: $orderSnapshot,
            placedAt: $placedAt,
        );
    }

    /**
     * @param array<string, mixed> $orderSnapshot
     */
    public static function restore(
        int $id,
        int $clientId,
        array $orderSnapshot,
        DateTimeImmutable $placedAt,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            orderSnapshot: $orderSnapshot,
            placedAt: $placedAt,
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
            throw new \LogicException('Запись ещё не сохранена.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    /**
     * @return array<string, mixed>
     */
    public function orderSnapshot(): array
    {
        return $this->orderSnapshot;
    }

    public function placedAt(): DateTimeImmutable
    {
        return $this->placedAt;
    }
}
