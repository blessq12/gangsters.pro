<?php

namespace App\Domain\Admin\Enums;

enum AdminHub: string
{
    case Analytics = 'analytics';
    case Operations = 'operations';
    case Catalog = 'catalog';
    case Marketing = 'marketing';
    case Company = 'company';
}
