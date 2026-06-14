<?php

namespace App\Domain\Promotion\ValueObject;

use App\Domain\Promotion\Enum\GiftBenefitType;
use App\Domain\Promotion\Enum\PromotionOrderChannel;

/**
 * Правило подарка: порог суммы корзины по способу получения.
 * Подарок доступен при сумме строго больше minOrderAmountKopecks.
 */
final class GiftBenefitRule
{
    public function __construct(
        private readonly PromotionOrderChannel $orderChannel,
        private readonly int $minOrderAmountKopecks,
        private readonly GiftBenefitType $benefitType,
        private readonly bool $isActive,
    ) {
        if ($minOrderAmountKopecks <= 0) {
            throw new \InvalidArgumentException('Порог подарка должен быть положительным.');
        }
    }

    public function orderChannel(): PromotionOrderChannel
    {
        return $this->orderChannel;
    }

    public function minOrderAmountKopecks(): int
    {
        return $this->minOrderAmountKopecks;
    }

    public function benefitType(): GiftBenefitType
    {
        return $this->benefitType;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
