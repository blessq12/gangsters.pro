<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\CreateCategoryDTO;
use App\Application\Catalog\Presenter\AdminCategoryPresenter;
use App\Application\Catalog\Query\GetAdminCategoryDetailQuery;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use Illuminate\Support\Str;

final class CreateCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly AdminCategoryPresenter $presenter,
        private readonly GetAdminCategoryDetailQuery $categoryDetail,
    ) {
    }

    public function execute(CreateCategoryDTO $dto): array
    {
        $category = Category::create(
            name: $dto->name,
            slug: Str::slug($dto->name),
            sortOrder: $dto->sortOrder,
            isActive: $dto->isActive,
        );

        $this->categories->save($category);

        return $this->categoryDetail->execute((int) $category->id());
    }
}
