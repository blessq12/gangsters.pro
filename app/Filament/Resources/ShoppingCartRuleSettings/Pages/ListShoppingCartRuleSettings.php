<?php

namespace App\Filament\Resources\ShoppingCartRuleSettings\Pages;

use App\Filament\Resources\ShoppingCartRuleSettings\ShoppingCartRuleSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListShoppingCartRuleSettings extends ListRecords
{
    protected static string $resource = ShoppingCartRuleSettingResource::class;

    protected static ?string $title = 'Правила корзины';

    public function mount(): void
    {
        $this->redirect(
            ShoppingCartRuleSettingResource::getUrl('edit', [
                'record' => ShoppingCartRuleSettingResource::resolveSettingsRecord(),
            ]),
            navigate: true,
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
