<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\ValueObject\ProductImage;
use App\Domain\Catalog\ValueObject\ProductSetLine;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Infrastructure\Catalog\Model\PRD_ProductSetLine;
use App\Shared\ValueObject\Money;

final class CatalogProductSetMapper
{
    /**
     * @param  list<ProductSetLine>  $lines
     * @param  list<int>  $tagIds
     * @param  list<ProductImage>  $images
     */
    public function toDomain(PRD_Product $row, array $lines, array $tagIds = [], array $images = []): ?ProductSet
    {
        if ($lines === []) {
            return null;
        }

        return new ProductSet(
            id: (int) $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            status: $this->resolveStatus($row),
            price: Money::rubles((int) ($row->price ?? 0)),
            description: $row->description !== null ? (string) $row->description : null,
            lines: $lines,
            tagIds: $tagIds,
            images: $images,
        );
    }

    public function mapLine(PRD_ProductSetLine $row): ProductSetLine
    {
        return new ProductSetLine(
            productId: (int) $row->product_id,
            quantity: (int) $row->quantity,
        );
    }

    private function resolveStatus(PRD_Product $row): ProductStatus
    {
        if ($row->archived_at !== null) {
            return ProductStatus::Archived;
        }

        $status = strtolower((string) $row->status);

        return $status === ProductStatus::Active->value
            ? ProductStatus::Active
            : ProductStatus::Archived;
    }
}
