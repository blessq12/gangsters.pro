<?php

namespace App\Filament\Support;

use Filament\Support\Contracts\HasLabel;

/**
 * Семантические группы бокового меню админ-панели (порядок = порядок cases).
 * Иконки только у пунктов меню — Filament не допускает иконки и у группы, и у items.
 */
enum AdminNavigationGroup: string implements HasLabel
{
    case Merchandising = 'merchandising';
    case Service = 'service';
    case Sales = 'sales';
    case Organization = 'organization';

    public function getLabel(): string
    {
        return match ($this) {
            self::Merchandising => 'Витрина',
            self::Service => 'Сервис',
            self::Sales => 'Продажи',
            self::Organization => 'Компания',
        };
    }
}
