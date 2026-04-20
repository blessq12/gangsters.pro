<?php

namespace App\Domain\Shopping\CartRules;

enum CartLineOrigin: string
{
    case User = 'user';
    case System = 'system';
}
