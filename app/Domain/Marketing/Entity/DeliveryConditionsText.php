<?php

namespace App\Domain\Marketing\Entity;

/**
 * Текст условий доставки для витрины.
 */
final class DeliveryConditionsText
{
    public function __construct(
        private readonly int $id,
        private readonly string $body,
        private readonly bool $isActive,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
