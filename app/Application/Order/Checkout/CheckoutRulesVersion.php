<?php

namespace App\Application\Order\Checkout;

interface CheckoutRulesVersion
{
    /**
     * Хеш всех данных, по которым фронт считает корзину филиала.
     */
    public function forBranch(int $branchId): string;
}
