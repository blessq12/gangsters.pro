<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SaveCategoryDto;
use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Repository\CategoryRepository;

final class SaveCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveCategoryDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название категории.');
        }

        $category = $this->categories->save(
            $dto->id,
            $name,
            $dto->isActive,
            $dto->isAccompanying,
        );

        if ($dto->items !== null) {
            $this->categories->replaceItems($category->id(), $dto->items);
        }

        return $this->presenter->category($category);
    }
}
