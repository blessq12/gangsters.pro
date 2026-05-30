<?php

namespace App\Application\Catalog\Contracts;

interface ProductDeletionGuardPort
{
    public function assertDeletable(int $productId): void;
}
