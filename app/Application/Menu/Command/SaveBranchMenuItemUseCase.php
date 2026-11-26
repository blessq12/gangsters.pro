<?php

namespace App\Application\Menu\Command;

use App\Application\Menu\DTO\SaveBranchMenuItemDto;
use App\Application\Menu\Presenter\BranchMenuPresenter;
use App\Domain\Supporting\Menu\Entity\BranchMenuItem;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Port\MenuCatalogLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchMenuRepository;

/**
 * Upsert позиции меню филиала: доступность и продажная цена.
 */
final class SaveBranchMenuItemUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly MenuCatalogLookupPort $catalog,
        private readonly BranchMenuRepository $menu,
        private readonly BranchMenuPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchMenuItemDto $dto): array
    {
        $this->branches->assertBranchExists($dto->branchId);

        if (! $this->catalog->productExists($dto->catalogItemId)) {
            throw new \InvalidArgumentException('Товар каталога не найден.');
        }

        $saved = $this->menu->upsert(new BranchMenuItem(
            branchId: $dto->branchId,
            catalogItemId: $dto->catalogItemId,
            isAvailable: $dto->isAvailable,
            priceKopecks: $dto->priceKopecks,
        ));

        return $this->presenter->item($saved);
    }
}
