<?php

namespace App\Infrastructure\Promotion\Listener;

use App\Domain\Catalog\Event\CatalogItemPriceChanged;
use App\Domain\Promotion\Repository\ProductPromotionRoleRepository;

/**
 * Catalog публикует новую цену; Promotion обновляет снимки name/price во всех ролях позиции.
 */
final class RefreshRoleSnapshotsOnCatalogItemPriceChanged
{
    public function __construct(
        private readonly ProductPromotionRoleRepository $roles,
    ) {}

    public function handle(CatalogItemPriceChanged $event): void
    {
        foreach ($this->roles->findByProductIds([$event->catalogItemId]) as $role) {
            $this->roles->save($role->withSnapshot($event->name, $event->regularPriceKopecks));
        }
    }
}
