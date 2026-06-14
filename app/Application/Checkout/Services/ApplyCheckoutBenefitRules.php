<?php

namespace App\Application\Checkout\Services;

use App\Domain\Checkout\Entity\Checkout;

/**
 * Применяет бизнес-правила акций к черновику checkout (мутация корзины).
 * Вызывается после заполнения блоков оформления и обязательно перед confirm.
 */
final class ApplyCheckoutBenefitRules
{
    public function __construct(
        private readonly SyncCheckoutComplementBenefitLines $complementBenefitLines,
    ) {}

    public function apply(Checkout $checkout): void
    {
        $this->complementBenefitLines->sync($checkout);
    }
}
