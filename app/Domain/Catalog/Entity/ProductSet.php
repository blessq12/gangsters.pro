<?php

namespace App\Domain\Catalog\Entity;

use App\Domain\Catalog\Contract\CatalogItem;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\ValueObject\ProductSetLine;
use App\Domain\Catalog\ValueObject\ProductImage;
use App\Shared\ValueObject\Money;

/**
 * Набор — группа товаров, объединённых в одну витринную позицию.
 */
final class ProductSet implements CatalogItem
{
    /**
     * @param  list<ProductSetLine>  $lines
     * @param  list<int>  $tagIds
     * @param  list<ProductImage>  $images
     */
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?string $sku,
        private readonly ProductStatus $status,
        private readonly Money $price,
        private readonly ?string $description,
        private readonly array $lines,
        private readonly array $tagIds,
        private readonly array $images = [],
    ) {
        if ($lines === []) {
            throw new \InvalidArgumentException('Набор должен содержать хотя бы один товар.');
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function kind(): CatalogItemKind
    {
        return CatalogItemKind::Set;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function sku(): ?string
    {
        return $this->sku;
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

    /**
     * @return list<ProductSetLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return list<int>
     */
    public function tagIds(): array
    {
        return $this->tagIds;
    }

    /**
     * @return list<ProductImage>
     */
    public function images(): array
    {
        return $this->images;
    }
}
