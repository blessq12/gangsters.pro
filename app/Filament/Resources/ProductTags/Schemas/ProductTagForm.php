<?php

namespace App\Filament\Resources\ProductTags\Schemas;

use App\Support\Product\TagColorLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')
                ->label('Текст бейджа')
                ->required()
                ->maxLength(255),
            TextInput::make('code')
                ->label('Код (auto, kebab)')
                ->disabled()
                ->dehydrated(false),
            Select::make('color')
                ->label('Цвет бейджа')
                ->required()
                ->default('amber')
                ->options(TagColorLabels::options()),
            Toggle::make('is_active')
                ->label('Активен')
                ->default(true),
        ]);
    }
}
