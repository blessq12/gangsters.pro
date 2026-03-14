<?php

namespace App\Infrastructure\Category\Repository;

use App\Domain\Category\Entity\Category as CategoryEntity;
use App\Domain\Category\Entity\CategoryProduct as CategoryProductEntity;
use App\Domain\Category\Repository\CategoryRepository as CategoryRepositoryContract;
use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use App\Services\Slug\TransliteratingSlugGenerator;
use DateTimeImmutable;

class CategoryRepository implements CategoryRepositoryContract
{
    public function findById(int $id): ?CategoryEntity
    {
        $model = PRD_Category::find($id);

        return $model ? $this->mapToEntity($model) : null;
    }

    public function findAllOrdered(): array
    {
        $models = PRD_Category::orderBy('sort_order')->get();

        return $models
            ->map(fn (PRD_Category $model) => $this->mapToEntity($model))
            ->all();
    }

    public function save(CategoryEntity $category): void
    {
        $model = $category->id()
            ? PRD_Category::findOrFail($category->id())
            : new PRD_Category();

        $model->name = $category->name();
        $model->slug = app(TransliteratingSlugGenerator::class)->uniqueFrom(
            $category->name(),
            PRD_Category::class,
            $category->id()
        );
        $model->sort_order = $category->sortOrder();
        $model->is_active = $category->isActive();

        $model->save();

        if ($category->id() === null) {
            $ref = new \ReflectionClass($category);
            $prop = $ref->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($category, $model->id);
        }
    }

    public function delete(CategoryEntity $category): void
    {
        if ($category->id() === null) {
            return;
        }

        PRD_Category::whereKey($category->id())->delete();
    }

    public function findLinksByCategoryId(int $categoryId): array
    {
        $models = PRD_CategoryProduct::where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->get();

        return $models
            ->map(fn (PRD_CategoryProduct $model) => $this->mapLinkToEntity($model))
            ->all();
    }

    public function saveLink(CategoryProductEntity $link): void
    {
        $model = $link->id()
            ? PRD_CategoryProduct::findOrFail($link->id())
            : new PRD_CategoryProduct();

        $model->category_id = $link->categoryId();
        $model->product_id = $link->productId();
        $model->sort_order = $link->sortOrder();

        $model->save();

        if ($link->id() === null) {
            $ref = new \ReflectionClass($link);
            $prop = $ref->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($link, $model->id);
        }
    }

    public function deleteLink(CategoryProductEntity $link): void
    {
        if ($link->id() === null) {
            return;
        }

        PRD_CategoryProduct::whereKey($link->id())->delete();
    }

    private function mapToEntity(PRD_Category $model): CategoryEntity
    {
        $createdAt = new DateTimeImmutable($model->created_at);
        $updatedAt = new DateTimeImmutable($model->updated_at);

        $ref = new \ReflectionClass(CategoryEntity::class);
        /** @var CategoryEntity $category */
        $category = $ref->newInstanceWithoutConstructor();

        $this->setCategoryProperty($category, 'id', $model->id);
        $this->setCategoryProperty($category, 'name', $model->name);
        $this->setCategoryProperty($category, 'slug', $model->slug);
        $this->setCategoryProperty($category, 'sortOrder', (int) $model->sort_order);
        $this->setCategoryProperty($category, 'isActive', (bool) $model->is_active);
        $this->setCategoryProperty($category, 'createdAt', $createdAt);
        $this->setCategoryProperty($category, 'updatedAt', $updatedAt);

        return $category;
    }

    private function mapLinkToEntity(PRD_CategoryProduct $model): CategoryProductEntity
    {
        $ref = new \ReflectionClass(CategoryProductEntity::class);
        /** @var CategoryProductEntity $link */
        $link = $ref->newInstanceWithoutConstructor();

        $this->setLinkProperty($link, 'id', $model->id);
        $this->setLinkProperty($link, 'categoryId', $model->category_id);
        $this->setLinkProperty($link, 'productId', $model->product_id);
        $this->setLinkProperty($link, 'sortOrder', (int) $model->sort_order);

        return $link;
    }

    private function setCategoryProperty(CategoryEntity $category, string $property, mixed $value): void
    {
        $ref = new \ReflectionProperty(CategoryEntity::class, $property);
        $ref->setAccessible(true);
        $ref->setValue($category, $value);
    }

    private function setLinkProperty(CategoryProductEntity $link, string $property, mixed $value): void
    {
        $ref = new \ReflectionProperty(CategoryProductEntity::class, $property);
        $ref->setAccessible(true);
        $ref->setValue($link, $value);
    }
}

