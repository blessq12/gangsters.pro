<?php

namespace Tests\Feature\Catalog;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;

final class InMemoryCategoryRepository implements CategoryRepository
{
    /** @var array<int, Category> */
    private array $categories = [];

    /** @var array<int, CategoryProduct> */
    private array $links = [];

    private int $categoryAutoIncrement = 1;
    private int $linkAutoIncrement = 1;

    public function findById(int $id): ?Category
    {
        return $this->categories[$id] ?? null;
    }

    public function findAllOrdered(): array
    {
        $categories = array_values($this->categories);

        usort(
            $categories,
            static fn (Category $a, Category $b) => $a->sortOrder() <=> $b->sortOrder(),
        );

        return $categories;
    }

    public function save(Category $category): void
    {
        $id = $category->id() ?? $this->categoryAutoIncrement++;

        $ref = new \ReflectionClass($category);
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($category, $id);

        $this->categories[$id] = $category;
    }

    public function delete(Category $category): void
    {
        $id = $category->id();
        if ($id === null) {
            return;
        }

        unset($this->categories[$id]);
    }

    public function findLinksByCategoryId(int $categoryId): array
    {
        return array_values(
            array_filter(
                $this->links,
                static fn (CategoryProduct $link) => $link->categoryId() === $categoryId,
            ),
        );
    }

    public function saveLink(CategoryProduct $link): void
    {
        $id = $link->id() ?? $this->linkAutoIncrement++;

        $ref = new \ReflectionClass($link);
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($link, $id);

        $this->links[$id] = $link;
    }

    public function deleteLink(CategoryProduct $link): void
    {
        $id = $link->id();
        if ($id === null) {
            return;
        }

        unset($this->links[$id]);
    }

    // Утилиты для тестов

    public function addCategory(Category $category): Category
    {
        $this->save($category);

        return $category;
    }

    public function addLink(CategoryProduct $link): CategoryProduct
    {
        $this->saveLink($link);

        return $link;
    }
}

