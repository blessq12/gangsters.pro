<?php

namespace App\Domain\Core\Order\ValueObject;

/**
 * Входные факты для OrderFormation без хождения в другие контексты.
 */
final readonly class OrderFormationFacts
{
    /**
     * @param  array<int, bool>  $isRollByProductId
     * @param  list<array{product_id: int, name: string}>  $complements
     * @param  array{product_id: int, name: string}|null  $gift
     */
    public function __construct(
        private array $isRollByProductId,
        private array $complements,
        private ?array $gift,
    ) {}

    public static function empty(): self
    {
        return new self([], [], null);
    }

    public function isRoll(int $productId): bool
    {
        return (bool) ($this->isRollByProductId[$productId] ?? false);
    }

    /**
     * @return list<array{product_id: int, name: string}>
     */
    public function complements(): array
    {
        return $this->complements;
    }

    /**
     * @return array{product_id: int, name: string}|null
     */
    public function gift(): ?array
    {
        return $this->gift;
    }
}
