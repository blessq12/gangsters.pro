<?php

namespace App\Domain\Core\Delivery\Routing\Entity;

/**
 * Остановка маршрутного листа: заказ + исход.
 */
final class RouteStop
{
    /** Заказ отменён после выпуска листа: курьер его не везёт. */
    public const OUTCOME_CANCELLED = 'cancelled';

    public function __construct(
        private readonly int $orderId,
        private readonly int $position,
        private readonly string $outcome,
        private readonly string $reason,
    ) {
        if ($this->orderId < 1) {
            throw new \InvalidArgumentException('orderId должен быть положительным.');
        }

        if ($this->position < 1) {
            throw new \InvalidArgumentException('position должен быть положительным.');
        }
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function outcome(): string
    {
        return $this->outcome;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function withOutcome(string $outcome, string $reason = ''): self
    {
        return new self(
            $this->orderId,
            $this->position,
            $outcome,
            $reason,
        );
    }
}
