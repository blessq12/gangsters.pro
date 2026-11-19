<?php

namespace App\Domain\Supporting\Catalog\Enum;

enum ProductStatus: string
{
    case Active = 'active';
    case Archived = 'archived';

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
