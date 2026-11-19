<?php

namespace App\Domain\Supporting\Catalog\Enum;

enum CatalogItemKind: string
{
    case Product = 'product';
    case Set = 'set';
}
