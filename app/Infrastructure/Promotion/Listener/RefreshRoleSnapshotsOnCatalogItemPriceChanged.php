<?php

namespace App\Infrastructure\Promotion\Listener;

use App\Domain\Core\Order\Repository\ProductPromotionRoleRepository;
use App\Domain\Supporting\Catalog\Event\CatalogItemPriceChanged;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;

/**
 * Catalog публикует новую цену; Promotion обновляет снимки name/price во всех ролях позиции
 * в каждом филиале компании.
 */
final class RefreshRoleSnapshotsOnCatalogItemPriceChanged
{
    public function __construct(
        private readonly ProductPromotionRoleRepository $roles,
        private readonly BranchRepository $branches,
    ) {}

    public function handle(CatalogItemPriceChanged $event): void
    {
        foreach ($this->branches->listByCompany(CompanyRepository::SINGLETON_ID) as $branch) {
            $branchRoles = $this->roles->findByBranchAndProductIds($branch->id(), [$event->catalogItemId]);
            foreach ($branchRoles as $role) {
                $this->roles->save($role->withSnapshot($event->name, $event->regularPriceKopecks));
            }
        }
    }
}
