<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Order\Port\CatalogComplementSetCandidate;
use App\Domain\Order\Port\CatalogComplementSetCandidatesPort;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Infrastructure\Catalog\Model\PRD_ProductImage;
use Illuminate\Support\Facades\Storage;

final class CatalogComplementSetCandidatesAdapter implements CatalogComplementSetCandidatesPort
{
    public function listActiveComplementSetCandidates(): array
    {
        $rows = PRD_Product::query()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->where('meta_is_complement_set', true)
            ->whereNull('archived_at')
            ->orderBy('name')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $productIds = $rows
            ->map(static fn (PRD_Product $row): int => (int) $row->id)
            ->all();

        $imageUrlByProductId = $this->resolvePrimaryImageUrls($productIds);

        return $rows
            ->map(function (PRD_Product $row) use ($imageUrlByProductId): CatalogComplementSetCandidate {
                $productId = (int) $row->id;

                return new CatalogComplementSetCandidate(
                    productId: $productId,
                    productName: (string) $row->name,
                    priceRubles: (int) ($row->price ?? 0),
                    imageUrl: $imageUrlByProductId[$productId] ?? null,
                );
            })
            ->all();
    }

    /**
     * @param  list<int>  $productIds
     * @return array<int, string>
     */
    private function resolvePrimaryImageUrls(array $productIds): array
    {
        $images = PRD_ProductImage::query()
            ->whereIn('product_id', $productIds)
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->get();

        $urls = [];

        foreach ($images as $image) {
            $productId = (int) $image->product_id;

            if (isset($urls[$productId])) {
                continue;
            }

            $path = is_string($image->path) ? trim($image->path) : '';
            if ($path === '') {
                continue;
            }

            $disk = is_string($image->disk) && $image->disk !== '' ? $image->disk : 'public';
            $urls[$productId] = Storage::disk($disk)->url($path);
        }

        return $urls;
    }
}
