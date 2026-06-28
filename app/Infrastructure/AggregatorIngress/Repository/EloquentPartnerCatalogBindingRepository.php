<?php

namespace App\Infrastructure\AggregatorIngress\Repository;

use App\Domain\AggregatorIngress\Repository\PartnerCatalogBindingRepository;
use App\Domain\AggregatorIngress\ValueObject\ResolvedPartnerProduct;
use App\Infrastructure\AggregatorIngress\Model\ING_PartnerSkuBinding;
use App\Infrastructure\Catalog\Model\PRD_Product;

final class EloquentPartnerCatalogBindingRepository implements PartnerCatalogBindingRepository
{
    public function resolve(string $partnerCode, string $partnerSku): ?ResolvedPartnerProduct
    {
        $binding = ING_PartnerSkuBinding::query()
            ->where('partner_code', $partnerCode)
            ->where('partner_sku', $partnerSku)
            ->first();

        if (! $binding instanceof ING_PartnerSkuBinding) {
            return null;
        }

        $product = PRD_Product::query()->find((int) $binding->product_id);
        if (! $product instanceof PRD_Product) {
            return null;
        }

        return new ResolvedPartnerProduct(
            productId: (int) $product->id,
            productName: (string) $product->name,
            sku: is_string($product->sku) && trim($product->sku) !== '' ? trim($product->sku) : null,
        );
    }

    public function listByPartner(string $partnerCode): array
    {
        $rows = ING_PartnerSkuBinding::query()
            ->where('partner_code', $partnerCode)
            ->orderBy('id')
            ->get(['partner_sku', 'product_id']);

        $bindings = [];

        foreach ($rows as $row) {
            if (! $row instanceof ING_PartnerSkuBinding) {
                continue;
            }

            $partnerSku = trim((string) $row->partner_sku);
            if ($partnerSku === '') {
                continue;
            }

            $bindings[] = [
                'partner_sku' => $partnerSku,
                'product_id' => (int) $row->product_id,
            ];
        }

        return $bindings;
    }
}
