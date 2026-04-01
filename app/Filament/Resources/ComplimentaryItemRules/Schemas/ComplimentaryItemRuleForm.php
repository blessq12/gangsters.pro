<?php

namespace App\Filament\Resources\ComplimentaryItemRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ComplimentaryItemRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('triggerCategories')
                    ->label('Триггер-категории')
                    ->relationship('triggerCategories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('gift_product_id')
                    ->label('Бесплатный товар')
                    ->relationship('giftProduct', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('priority')
                    ->label('Приоритет')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Активно')
                    ->default(true),
            ]);
    }
}
