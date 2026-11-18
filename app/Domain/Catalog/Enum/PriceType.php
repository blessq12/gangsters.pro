<?php

namespace App\Domain\Catalog\Enum;

enum PriceType: string
{
    case Regular = 'regular';
    case Old = 'old';
    case Promo = 'promo';
}
