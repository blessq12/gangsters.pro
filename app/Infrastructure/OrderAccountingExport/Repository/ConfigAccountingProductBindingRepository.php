<?php

namespace App\Infrastructure\OrderAccountingExport\Repository;

use App\Domain\OrderAccountingExport\Repository\AccountingProductBindingRepository;

final class ConfigAccountingProductBindingRepository implements AccountingProductBindingRepository
{
    public function resolveExternalProductId(string $systemCode, int $productId): ?string
    {
        $bindings = config("order-accounting-export.systems.{$systemCode}.product_bindings", []);
        if (! is_array($bindings)) {
            return null;
        }

        $value = $bindings[(string) $productId] ?? $bindings[$productId] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
