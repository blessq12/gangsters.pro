<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveBranchMenuItemDto;
use App\Application\Company\Presenter\BranchMenuPresenter;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Upsert позиции меню филиала: доступность и продажная цена.
 */
final class SaveBranchMenuItemUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly CatalogItemRepository $catalog,
        private readonly BranchMenuRepository $menu,
        private readonly BranchMenuPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchMenuItemDto $dto): array
    {
        if ($this->branches->findById($dto->branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        if ($this->catalog->findProductById($dto->catalogItemId) === null) {
            throw new \InvalidArgumentException('Товар каталога не найден.');
        }

        $saved = $this->menu->upsert(new BranchMenuItem(
            branchId: $dto->branchId,
            catalogItemId: $dto->catalogItemId,
            isAvailable: $dto->isAvailable,
            priceKopecks: $dto->priceKopecks,
            isGiftCandidate: $dto->isGiftCandidate,
        ));

        return $this->presenter->item($saved);
    }
}
