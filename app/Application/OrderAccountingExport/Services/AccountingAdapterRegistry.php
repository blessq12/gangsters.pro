<?php

namespace App\Application\OrderAccountingExport\Services;

use App\Application\OrderAccountingExport\Port\AccountingSystemAdapter;
use App\Domain\OrderAccountingExport\Exception\AccountingSystemNotConfiguredException;

final class AccountingAdapterRegistry
{
    /**
     * @param  iterable<AccountingSystemAdapter>  $adapters
     */
    public function __construct(
        private readonly iterable $adapters,
    ) {}

    public function resolve(string $systemCode): AccountingSystemAdapter
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->systemCode() === $systemCode) {
                return $adapter;
            }
        }

        throw new AccountingSystemNotConfiguredException($systemCode);
    }

    /**
     * @return list<AccountingSystemAdapter>
     */
    public function enabled(): array
    {
        if (! (bool) config('order-accounting-export.enabled', true)) {
            return [];
        }

        $enabled = [];

        foreach ($this->adapters as $adapter) {
            if ($adapter->isEnabled()) {
                $enabled[] = $adapter;
            }
        }

        return $enabled;
    }
}
