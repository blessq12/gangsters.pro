<?php

namespace App\Application\Menu\Presenter;

use App\Domain\Supporting\Menu\Entity\BranchMenuItem;

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
            'price_kopecks' => $item->priceKopecks(),
            'price_rubles' => intdiv($item->priceKopecks(), 100),
        ];
    }
}
