<?php

namespace App\Filament\Resources\ShoppingCartRuleSettings\Schemas;

use App\Support\Money;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShoppingCartRuleSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Правило «комплект к роллам»')
                ->description('Считает единицы с флагом «учитывать в комплекте (ролл)» в карточке товара. Системная позиция «комплект» — товар с флагом «товар комплекта» (ровно один активный с этим флагом).')
                ->schema([
                    Toggle::make('complement_rule_enabled')
                        ->label('Включено')
                        ->default(true),
                    TextInput::make('complement_rule_sort')
                        ->label('Порядок в конвейере (меньше — раньше)')
                        ->numeric()
                        ->minValue(0)
                        ->default(10)
                        ->required(),
                    TextInput::make('rolls_per_complement')
                        ->label('Единиц «ролла» на один комплект')
                        ->numeric()
                        ->minValue(1)
                        ->default(2)
                        ->required(),
                ])
                ->columnSpanFull(),
            Section::make('Правило «подарок от суммы»')
                ->description('Если сумма пользовательских позиций ≥ порога, в API появляется promo_state. Кандидаты в подарок — товары с флагом «доступен как подарок» в карточке. Выбор: checkout_draft.promotions.free_roll_gift_product_id.')
                ->schema([
                    Toggle::make('gift_rule_enabled')
                        ->label('Включено')
                        ->default(true),
                    TextInput::make('gift_rule_sort')
                        ->label('Порядок в конвейере')
                        ->numeric()
                        ->minValue(0)
                        ->default(20)
                        ->required(),
                    TextInput::make('gift_threshold_kopecks')
                        ->label('Порог суммы, ₽')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->required()
                        ->formatStateUsing(function ($state): ?float {
                            if ($state === null || $state === '') {
                                return null;
                            }

                            return Money::kopecksToApiRubles((int) $state);
                        })
                        ->dehydrateStateUsing(function ($state): ?int {
                            return Money::apiRublesToKopecks($state);
                        }),
                ])
                ->columnSpanFull(),
        ]);
    }
}
