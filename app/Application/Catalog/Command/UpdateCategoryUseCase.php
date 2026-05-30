<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\UpdateCategoryDTO;
use App\Application\Catalog\Query\GetAdminCategoryDetailQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Category\Repository\CategoryRepository;

final class UpdateCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly GetAdminCategoryDetailQuery $categoryDetail,
    ) {
    }

    public function execute(UpdateCategoryDTO $dto): array
    {
        $category = $this->categories->findById($dto->categoryId);
        if ($category === null) {
            throw new ApiException('Category not found.', 404);
        }

        $category->rename($dto->name);
        $category->changeSortOrder($dto->sortOrder);

        if ($dto->isActive) {
            $category->activate();
        } else {
            $category->deactivate();
        }

        $this->categories->save($category);

        return $this->categoryDetail->execute($dto->categoryId);
    }
}
