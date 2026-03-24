<?php

namespace App\Infrastructure\Product\Repository;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Domain\Product\Entity\ProductImage;
use App\Domain\Product\Entity\ProductIngredient;
use App\Domain\Product\Repository\ProductRepository as ProductRepositoryContract;
use App\Domain\Product\VO\CustomerStatus;
use App\Domain\Product\VO\ImageVariant;
use App\Domain\Product\VO\Nutrition;
use App\Domain\Product\VO\Price;
use App\Domain\Product\VO\ProductTag;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_ProductImage;
use App\Infrastructure\Product\Model\PRD_ProductIngredient;
use App\Infrastructure\Product\Model\PRD_ProductPrice;
use App\Infrastructure\Product\Model\PRD_ProductTag;
use App\Services\Slug\TransliteratingSlugGenerator;
use DateTimeImmutable;

class ProductRepository implements ProductRepositoryContract
{
    public function findById(int $id): ?ProductEntity
    {
        $model = PRD_Product::with(['images', 'ingredients', 'tags', 'prices'])->find($id);

        return $model ? $this->mapToEntity($model) : null;
    }

    public function findByIds(array $ids): array
    {
        $models = PRD_Product::with(['images', 'ingredients', 'tags', 'prices'])
            ->whereIn('id', $ids)
            ->get();

        return $models
            ->map(fn (PRD_Product $model) => $this->mapToEntity($model))
            ->all();
    }

    public function findByCategoryId(int $categoryId): array
    {
        // В текущей реализации поиск по категории делается через CategoryRepository и CategoryProduct,
        // поэтому здесь оставляем заглушку.
        return [];
    }

    public function findNonActive(): array
    {
        $models = PRD_Product::with(['images', 'ingredients', 'tags', 'prices'])
            ->where('status', '!=', ProductEntity::STATUS_ACTIVE)
            ->get();

        return $models
            ->map(fn (PRD_Product $model) => $this->mapToEntity($model))
            ->all();
    }

    public function save(ProductEntity $product): void
    {
        $model = $product->id()
            ? PRD_Product::findOrFail($product->id())
            : new PRD_Product();

        $model->name = $product->name();
        $model->slug = app(TransliteratingSlugGenerator::class)->uniqueFrom(
            $product->name(),
            PRD_Product::class,
            $product->id()
        );
        $model->articul = $product->articul();
        $model->description = $product->description();

        $nutrition = $product->nutrition();
        $model->calories = $nutrition->calories();
        $model->proteins = $nutrition->proteins();
        $model->fats = $nutrition->fats();
        $model->carbs = $nutrition->carbs();
        $model->nutrition_basis = $nutrition->basis();

        $model->status = $product->status();
        $model->archived_at = $product->archivedAt()?->format('Y-m-d H:i:s');

        $model->save();

        // Синхронизируем ID обратно в доменную сущность
        if ($product->id() === null) {
            $product->assignPersistedId((int) $model->id);
        }

        // Для MVP: полная пересборка коллекций
        $model->images()->delete();
        $model->ingredients()->delete();
        $model->tags()->delete();
        $model->prices()->delete();

        foreach ($product->images() as $image) {
            $this->saveImage($model, $image);
        }

        foreach ($product->ingredients() as $ingredient) {
            $this->saveIngredient($model, $ingredient);
        }

        foreach ($product->tags() as $tag) {
            $this->saveTag($model, $tag);
        }

        foreach ($product->prices() as $price) {
            $this->savePrice($model, $price);
        }
    }

    public function delete(ProductEntity $product): void
    {
        if ($product->id() === null) {
            return;
        }

        PRD_Product::whereKey($product->id())->delete();
    }

    private function saveImage(PRD_Product $productModel, ProductImage $image): void
    {
        /** @var PRD_ProductImage $model */
        $model = $productModel->images()->make();
        $model->sort_order = $image->sortOrder();

        foreach ($image->variants() as $variant) {
            $size = $variant->size();
            if ($size === 'thumb') {
                $model->thumb_path = $variant->path();
                $model->thumb_width = $variant->width();
                $model->thumb_height = $variant->height();
            } elseif ($size === 'medium') {
                $model->medium_path = $variant->path();
                $model->medium_width = $variant->width();
                $model->medium_height = $variant->height();
            } elseif ($size === 'large') {
                $model->large_path = $variant->path();
                $model->large_width = $variant->width();
                $model->large_height = $variant->height();
            }
        }

        $model->save();
    }

    private function saveIngredient(PRD_Product $productModel, ProductIngredient $ingredient): void
    {
        /** @var PRD_ProductIngredient $model */
        $model = $productModel->ingredients()->make();
        $model->name = $ingredient->name();
        $model->amount = $ingredient->amount();
        $model->unit = $ingredient->unit();
        $model->is_allergen = $ingredient->isAllergen();
        $model->save();
    }

    private function saveTag(PRD_Product $productModel, ProductTag $tag): void
    {
        /** @var PRD_ProductTag $model */
        $model = $productModel->tags()->make();
        $model->code = $tag->code();
        $model->save();
    }

    private function savePrice(PRD_Product $productModel, Price $price): void
    {
        /** @var PRD_ProductPrice $model */
        $model = $productModel->prices()->make();
        $model->amount = $price->amount();
        $model->customer_status = $price->customerStatus()->code();
        $model->is_default = $price->isDefault();
        $model->save();
    }

    private function mapToEntity(PRD_Product $model): ProductEntity
    {
        $nutrition = new Nutrition(
            calories: (float) $model->calories,
            proteins: (float) $model->proteins,
            fats: (float) $model->fats,
            carbs: (float) $model->carbs,
            basis: $model->nutrition_basis,
        );

        $images = $model->images
            ->map(fn (PRD_ProductImage $imageModel) => $this->mapImageToEntity($imageModel))
            ->all();

        $ingredients = $model->ingredients
            ->map(fn (PRD_ProductIngredient $ingredientModel) => $this->mapIngredientToEntity($ingredientModel))
            ->all();

        $tags = $model->tags
            ->map(fn (PRD_ProductTag $tagModel) => new ProductTag($tagModel->code))
            ->all();

        $prices = $model->prices
            ->map(fn (PRD_ProductPrice $priceModel) => new Price(
                amount: (int) $priceModel->amount,
                customerStatus: new CustomerStatus($priceModel->customer_status),
                isDefault: (bool) $priceModel->is_default,
            ))
            ->all();

        $createdAt = new DateTimeImmutable($model->created_at);
        $updatedAt = new DateTimeImmutable($model->updated_at);
        $archivedAt = $model->archived_at
            ? new DateTimeImmutable($model->archived_at)
            : null;

        return ProductEntity::reconstitute(
            id: (int) $model->id,
            name: $model->name,
            articul: $model->articul,
            description: $model->description ?? '',
            nutrition: $nutrition,
            images: $images,
            ingredients: $ingredients,
            tags: $tags,
            prices: $prices,
            status: $model->status,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            archivedAt: $archivedAt,
        );
    }

    private function mapImageToEntity(PRD_ProductImage $model): ProductImage
    {
        $variants = [];

        if ($model->thumb_path) {
            $variants[] = new ImageVariant('thumb', $model->thumb_path, (int) $model->thumb_width, (int) $model->thumb_height);
        }

        if ($model->medium_path) {
            $variants[] = new ImageVariant('medium', $model->medium_path, (int) $model->medium_width, (int) $model->medium_height);
        }

        if ($model->large_path) {
            $variants[] = new ImageVariant('large', $model->large_path, (int) $model->large_width, (int) $model->large_height);
        }

        return ProductImage::create(
            variants: $variants,
            sortOrder: $model->sort_order,
        );
    }

    private function mapIngredientToEntity(PRD_ProductIngredient $model): ProductIngredient
    {
        return ProductIngredient::create(
            name: $model->name,
            amount: $model->amount,
            unit: $model->unit,
            isAllergen: (bool) $model->is_allergen,
        );
    }

}

