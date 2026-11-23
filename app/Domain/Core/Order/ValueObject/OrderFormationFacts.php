<?php

namespace App\Domain\Core\Order\ValueObject;

/**
 * Входные факты для OrderFormation без хождения в другие контексты.
 */
final readonly class OrderFormationFacts
{
    /**
     * @param  array<int, bool>  $isRollByProductId  флаги is_roll по id товара
     * @param  list<array{product_id: int, name: string}>  $complements
     * @param  list<array{product_id: int, name: string}>  $giftCandidates
     * @param  array{product_id: int, name: string}|null  $gift выбранный кандидат
     * @param  array<int, int>  $rollsPerUnitByLineId  сколько роллов даёт 1 шт. линии (товар или сет)
     * @param  array<int, int>  $complementSelections  productId → выбранное кол-во комплектного; пусто = максимум
     */
    public function __construct(
        private array $isRollByProductId,
        private array $complements,
        private array $giftCandidates = [],
        private ?array $gift = null,
        private array $rollsPerUnitByLineId = [],
        private array $complementSelections = [],
    ) {}

    public static function empty(): self
    {
        return new self([], [], [], null, [], []);
    }

    /**
     * Сколько штук комплектного положить при праве на $entitledSets.
     */
    public function complementQuantity(int $productId, int $entitledSets): int
    {
        if ($this->complementSelections === []) {
            return $entitledSets;
        }

        $selected = (int) ($this->complementSelections[$productId] ?? 0);

        return max(0, min($entitledSets, $selected));
    }

    public function isRoll(int $productId): bool
    {
        return (bool) ($this->isRollByProductId[$productId] ?? false);
    }

    /**
     * Сколько роллов даёт одна единица линии корзины (regular или set).
     */
    public function rollUnitsPerLineUnit(int $lineProductId): int
    {
        if (array_key_exists($lineProductId, $this->rollsPerUnitByLineId)) {
            return max(0, (int) $this->rollsPerUnitByLineId[$lineProductId]);
        }

        return $this->isRoll($lineProductId) ? 1 : 0;
    }

    /**
     * @return list<array{product_id: int, name: string}>
     */
    public function complements(): array
    {
        return $this->complements;
    }

    /**
     * @return list<array{product_id: int, name: string}>
     */
    public function giftCandidates(): array
    {
        return $this->giftCandidates;
    }

    /**
     * @return array{product_id: int, name: string}|null
     */
    public function gift(): ?array
    {
        return $this->gift;
    }
}
