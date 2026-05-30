<?php

namespace App\Application\Catalog\Contracts;

interface CategoryDeletionGuardPort
{
    public function assertDeletable(int $categoryId): void;
}
