<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SetCategoryProductsDTO;
use App\Application\Catalog\Query\GetAdminCategoryLayoutQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;

final class SetCategoryProductsUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly GetAdminCategoryLayoutQuery $layoutQuery,
    ) {
    }

    public function execute(SetCategoryProductsDTO $dto): array
    {
        $category = $this->categories->findById($dto->categoryId);
        if ($category === null) {
            throw new ApiException('Category not found.', 404);
        }

        $existingLinks = $this->categories->findLinksByCategoryId($dto->categoryId);
        $existingByProductId = [];
        foreach ($existingLinks as $link) {
            $existingByProductId[$link->productId()] = $link;
        }

        $desiredProductIds = array_values(array_unique($dto->productIds));

        foreach ($existingLinks as $link) {
            if (! in_array($link->productId(), $desiredProductIds, true)) {
                $this->categories->deleteLink($link);
            }
        }

        foreach ($desiredProductIds as $sortOrder => $productId) {
            $existing = $existingByProductId[$productId] ?? null;
            if ($existing !== null) {
                if ($existing->sortOrder() !== $sortOrder) {
                    $this->categories->deleteLink($existing);
                    $this->categories->saveLink(
                        CategoryProduct::link($dto->categoryId, $productId, $sortOrder),
                    );
                }

                continue;
            }

            $this->categories->saveLink(
                CategoryProduct::link($dto->categoryId, $productId, $sortOrder),
            );
        }

        return $this->layoutQuery->execute($dto->categoryId);
    }
}
