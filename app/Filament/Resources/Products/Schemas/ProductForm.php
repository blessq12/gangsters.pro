<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('visible')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->default('Название не задано'),
                Toggle::make('hit')
                    ->required(),
                Toggle::make('spicy')
                    ->required(),
                Toggle::make('kidsAllow')
                    ->required(),
                Toggle::make('onion')
                    ->required(),
                Toggle::make('garlic')
                    ->required(),
                Textarea::make('consist')
                    ->columnSpanFull(),
                TextInput::make('weight'),
                TextInput::make('price'),
                TextInput::make('vat')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sku')
                    ->label('SKU'),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
