<?php

namespace App\Domain\Supporting\Catalog\Enum;

enum PriceType: string
{
    case Regular = 'regular';
    case Old = 'old';
    case Promo = 'promo';
}
