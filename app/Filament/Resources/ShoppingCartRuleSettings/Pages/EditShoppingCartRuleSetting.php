<?php

namespace App\Filament\Resources\ShoppingCartRuleSettings\Pages;

use App\Filament\Resources\ShoppingCartRuleSettings\ShoppingCartRuleSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditShoppingCartRuleSetting extends EditRecord
{
    protected static string $resource = ShoppingCartRuleSettingResource::class;

    protected static ?string $title = 'Правила корзины';

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $k = $data['gift_threshold_kopecks'] ?? null;
        if (! is_int($k) || $k < 1) {
            $data['gift_threshold_kopecks'] = 180_000;
        }

        $data['rolls_per_complement'] = max(1, (int) ($data['rolls_per_complement'] ?? 2));

        return $data;
    }
}
