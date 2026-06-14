<?php

namespace App\Domain\Catalog\Entity;

use App\Domain\Catalog\Contract\CatalogItem;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\ValueObject\Nutrition;
use App\Shared\ValueObject\Money;

/**
 * Товар каталога.
 */
final class Product implements CatalogItem
{
    /**
     * @param  list<int>  $tagIds
     * @param  list<string>  $ingredients
     */
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly ProductStatus $status,
        private readonly Money $price,
        private readonly ?string $description,
        private readonly ?Nutrition $nutrition,
        private readonly array $tagIds,
        private readonly array $ingredients = [],
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function kind(): CatalogItemKind
    {
        return CatalogItemKind::Product;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function status(): ProductStatus
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function nutrition(): ?Nutrition
    {
        return $this->nutrition;
    }

    /**
     * @return list<int>
     */
    public function tagIds(): array
    {
        return $this->tagIds;
    }

    /**
     * @return list<string>
     */
    public function ingredients(): array
    {
        return $this->ingredients;
    }
}
