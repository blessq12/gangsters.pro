<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Supporting\Catalog\Entity\Price;
use App\Domain\Supporting\Catalog\Entity\ProductSet;
use App\Domain\Supporting\Catalog\Enum\PriceType;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\ValueObject\ProductImage;
use App\Domain\Supporting\Catalog\ValueObject\ProductSetLine;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Catalog\Model\CAT_Price;
use App\Infrastructure\Catalog\Model\CAT_SetLine;

final class CatalogProductSetMapper
{
    /**
     * @param  list<ProductSetLine>  $lines
     * @param  list<int>  $tagIds
     * @param  list<ProductImage>  $images
     * @param  list<Price>  $prices
     */
    public function toDomain(
        CAT_Item $row,
        array $lines,
        array $tagIds = [],
        array $images = [],
        array $prices = [],
    ): ?ProductSet {
        if ($lines === []) {
            return null;
        }

        return new ProductSet(
            id: (int) $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            sku: $this->resolveSku($row),
            status: $this->resolveStatus($row),
            prices: $prices !== [] ? $prices : $this->mapPrices($row),
            description: $row->description !== null ? (string) $row->description : null,
            lines: $lines,
            tagIds: $tagIds,
            images: $images,
        );
    }

    public function mapLine(CAT_SetLine $row): ProductSetLine
    {
        return new ProductSetLine(
            productId: (int) $row->component_item_id,
            quantity: (int) $row->quantity,
        );
    }

    /**
     * @return list<Price>
     */
    public function mapPrices(CAT_Item $row): array
    {
        $priceRows = $row->relationLoaded('prices')
            ? $row->prices
            : CAT_Price::query()->where('item_id', $row->id)->get();

        $prices = [];
        foreach ($priceRows as $priceRow) {
            if (! $priceRow instanceof CAT_Price) {
                continue;
            }

            $type = PriceType::tryFrom((string) $priceRow->type);
            if ($type === null) {
                continue;
            }

            $prices[] = Price::of(
                type: $type,
                amountKopecks: max(0, (int) $priceRow->amount_kopecks),
                id: (int) $priceRow->id,
            );
        }

        return $prices;
    }

    private function resolveSku(CAT_Item $row): ?string
    {
        $sku = trim((string) ($row->sku ?? ''));

        return $sku !== '' ? $sku : null;
    }

    private function resolveStatus(CAT_Item $row): ProductStatus
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
