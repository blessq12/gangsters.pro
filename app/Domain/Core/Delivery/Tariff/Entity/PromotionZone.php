<?php

namespace App\Domain\Core\Delivery\Tariff\Entity;

/**
 * Снимок зоны доставки для промо-логики оформления (тарифный контекст Delivery).
 */
final class PromotionZone
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly int $feeKopecks,
        private readonly bool $isRemote,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function feeKopecks(): int
    {
        return $this->feeKopecks;
    }

    public function isRemote(): bool
    {
        return $this->isRemote;
    }
}
