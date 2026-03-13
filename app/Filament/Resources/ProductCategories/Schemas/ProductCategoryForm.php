<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\TextInput;
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
                TextInput::make('slug')
                    ->required()
                    ->label('Slug'),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('Порядок'),
            ]);
    }
}
