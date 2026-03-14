<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Название'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Порядок'),
                Toggle::make('is_active')
                    ->label('Показывать на сайте')
                    ->default(true),
            ]);
    }
}
