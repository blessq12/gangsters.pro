<?php

namespace App\Domain\Catalog\Enum;

enum CatalogItemKind: string
{
    case Product = 'product';
    case Set = 'set';
}
