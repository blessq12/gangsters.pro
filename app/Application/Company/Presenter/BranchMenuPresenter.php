<?php

namespace App\Application\Company\Presenter;

use App\Domain\Supporting\Company\Entity\BranchMenuItem;

/**
 * Формат ответа меню филиала для админки.
 */
final class BranchMenuPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function item(BranchMenuItem $item): array
    {
        return [
            'branch_id' => $item->branchId(),
            'catalog_item_id' => $item->catalogItemId(),
            'is_available' => $item->isAvailable(),
            'is_gift_candidate' => $item->isGiftCandidate(),
            'price_kopecks' => $item->priceKopecks(),
            'price_rubles' => intdiv($item->priceKopecks(), 100),
        ];
    }
}
