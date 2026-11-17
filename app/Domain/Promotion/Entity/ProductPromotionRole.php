<?php

namespace App\Domain\Promotion\Entity;

/**
 * Роль товара в акциях: ролл, комплемент-сет, платный «близнец», кандидат в подарок.
 */
final class ProductPromotionRole
{
    public function __construct(
        private readonly int $productId,
        private readonly bool $countsAsRoll,
        private readonly bool $isComplementSet,
        private readonly ?int $paidTwinProductId,
        private readonly bool $isGiftCandidate,
    ) {}

    public static function restore(
        int $productId,
        bool $countsAsRoll,
        bool $isComplementSet,
        ?int $paidTwinProductId,
        bool $isGiftCandidate,
    ): self {
        return new self(
            $productId,
            $countsAsRoll,
            $isComplementSet,
            $paidTwinProductId,
            $isGiftCandidate,
        );
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function countsAsRoll(): bool
    {
        return $this->countsAsRoll;
    }

    public function isComplementSet(): bool
    {
        return $this->isComplementSet;
    }

    public function paidTwinProductId(): ?int
    {
        return $this->paidTwinProductId;
    }

    public function isGiftCandidate(): bool
    {
        return $this->isGiftCandidate;
    }
}
