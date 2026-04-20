<?php

namespace App\Domain\Shopping\CartRules\Contracts;

use App\Domain\Shopping\CartRules\ProductRuleView;

interface ProductRuleViewProviderInterface
{
    /**
     * @param  int[]  $productIds
     * @return array<int, ProductRuleView>
     */
    public function getViewsByProductIds(array $productIds): array;

    /**
     * Активные товары с флагом «кандидат в подарок».
     *
     * @return int[]
     */
    public function findActiveGiftCandidateProductIds(): array;

    /**
     * Все активные товары с флагом «товар комплекта».
     *
     * @return int[]
     */
    public function findActiveComplementSetProductIds(): array;
}
