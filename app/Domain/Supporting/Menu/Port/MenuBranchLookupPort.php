<?php

namespace App\Domain\Supporting\Menu\Port;

/**
 * Проверка филиала для меню. Menu Domain не импортирует Company/Branch repo.
 */
interface MenuBranchLookupPort
{
    public function assertBranchExists(int $branchId): void;

    public function assertBranchActive(int $branchId): void;
}
