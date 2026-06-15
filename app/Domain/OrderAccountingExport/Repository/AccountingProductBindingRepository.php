<?php

namespace App\Domain\OrderAccountingExport\Repository;

interface AccountingProductBindingRepository
{
    public function resolveExternalProductId(string $systemCode, int $productId): ?string;
}
