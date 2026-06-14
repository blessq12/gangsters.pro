<?php

namespace App\Domain\Promotion\ValueObject;

/**
 * Правило комплекта дополнений: за каждые N роллов в корзине — один комплект.
 * Роллы и наборы дополнений помечаются в каталоге (meta_counts_as_roll / meta_is_complement_set).
 */
final class ComplementSetBenefitRule
{
    public function __construct(
        private readonly int $rollsPerSet,
        private readonly bool $isActive,
    ) {
        if ($rollsPerSet < 1) {
            throw new \InvalidArgumentException('Количество роллов на комплект должно быть не меньше 1.');
        }
    }

    public function rollsPerSet(): int
    {
        return $this->rollsPerSet;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
