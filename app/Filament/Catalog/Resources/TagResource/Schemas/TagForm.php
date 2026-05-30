<?php

namespace App\Filament\Catalog\Resources\TagResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('label')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Select::make('color')
                    ->label('Цвет')
                    ->options([
                        'amber' => 'Янтарный',
                        'red' => 'Красный',
                        'green' => 'Зелёный',
                        'blue' => 'Синий',
                        'gray' => 'Серый',
                    ])
                    ->default('amber'),
                TextInput::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
                TextInput::make('code')
                    ->label('Код')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
            ]);
    }
}
