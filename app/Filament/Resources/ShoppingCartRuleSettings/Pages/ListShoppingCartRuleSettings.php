<?php

namespace App\Filament\Resources\ShoppingCartRuleSettings\Pages;

use App\Filament\Resources\ShoppingCartRuleSettings\ShoppingCartRuleSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListShoppingCartRuleSettings extends ListRecords
{
    protected static string $resource = ShoppingCartRuleSettingResource::class;

    protected static ?string $title = 'Правила корзины';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
