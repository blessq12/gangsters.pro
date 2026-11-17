<?php

namespace App\Infrastructure\Promotion\Repository;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Promotion\Repository\ProductPromotionRoleRepository;
use App\Infrastructure\Catalog\Model\PRD_Product;

/**
 * Роли товаров в акциях: читает колонки meta_* / paid_twin_product_id / is_system из PRD_Product.
 * Физический вынос колонок — отдельный план миграций.
 */
final class EloquentProductPromotionRoleRepository implements ProductPromotionRoleRepository
{
    public function findMetaByProductIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        return PRD_Product::query()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->whereIn('id', $ids)
            ->get(['id', 'meta_counts_as_roll', 'meta_is_complement_set', 'paid_twin_product_id'])
            ->mapWithKeys(static function (PRD_Product $row): array {
                $paidTwinId = $row->paid_twin_product_id !== null ? (int) $row->paid_twin_product_id : 0;

                return [
                    (int) $row->id => [
                        'counts_as_roll' => (bool) $row->meta_counts_as_roll,
                        'complement_set' => (bool) $row->meta_is_complement_set,
                        'paid_twin_product_id' => $paidTwinId > 0 ? $paidTwinId : null,
                    ],
                ];
            })
            ->all();
    }

    public function findPaidTwinProductIds(): array
    {
        return PRD_Product::query()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('meta_is_complement_set', true)
            ->whereNotNull('paid_twin_product_id')
            ->pluck('paid_twin_product_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function findActiveGiftCandidateProductIds(): array
    {
        return PRD_Product::query()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->where('is_system', true)
            ->whereNull('archived_at')
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    public function findActiveComplementSetProductIds(): array
    {
        return PRD_Product::query()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->where('meta_is_complement_set', true)
            ->whereNull('archived_at')
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            if (! is_numeric($id)) {
                continue;
            }

            $intId = (int) $id;
            if ($intId > 0) {
                $normalized[] = $intId;
            }
        }

        return array_values(array_unique($normalized));
    }
}
