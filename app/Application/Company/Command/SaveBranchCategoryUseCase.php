<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveBranchCategoryDto;
use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Application\Company\Service\BranchCategoryItemsValidator;
use App\Domain\Supporting\Company\Entity\BranchCategory;
use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Создание / обновление категории филиала (опционально вместе с позициями).
 */
final class SaveBranchCategoryUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchCategoryItemsValidator $itemsValidator,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchCategoryDto $dto): array
    {
        if ($this->branches->findById($dto->branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название категории.');
        }

        BranchCategory::assertExclusiveRoles(
            $dto->isAccompanying,
            $dto->isComplement,
            $dto->isGift,
        );

        foreach ($this->categories->listByBranch($dto->branchId) as $existing) {
            if ($dto->id !== null && $existing->id() === $dto->id) {
                continue;
            }
            if ($dto->isComplement && $existing->isComplement()) {
                throw new \InvalidArgumentException('В филиале уже есть категория «комплектные».');
            }
            if ($dto->isGift && $existing->isGift()) {
                throw new \InvalidArgumentException('В филиале уже есть категория «подарки».');
            }
        }

        if ($dto->items !== null) {
            $this->itemsValidator->assertValid($dto->branchId, $dto->items);
        }

        $saved = $this->categories->save(
            $dto->branchId,
            $dto->id,
            $name,
            (string) $dto->slug,
            $dto->sortOrder,
            $dto->isActive,
            $dto->isAccompanying,
            $dto->isComplement,
            $dto->isGift,
            $dto->items,
        );

        return $this->presenter->category($saved, $this->categories->findItemsByCategoryId($saved->id()));
    }
}
