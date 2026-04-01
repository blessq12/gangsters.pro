<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Название'),
                TextInput::make('articul')
                    ->label('Артикул')
                    ->maxLength(255),
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активен',
                        'archived' => 'Архив',
                    ])
                    ->required()
                    ->default('active'),
                Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Цена (в рублях)')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('calories')
                    ->numeric()
                    ->label('Ккал на 100г'),
                TextInput::make('proteins')
                    ->numeric()
                    ->label('Белки на 100г'),
                TextInput::make('fats')
                    ->numeric()
                    ->label('Жиры на 100г'),
                TextInput::make('carbs')
                    ->numeric()
                    ->label('Углеводы на 100г'),
                Select::make('tags')
                    ->label('Теги')
                    ->relationship('tags', 'label')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('label')
                            ->label('Текст бейджа')
                            ->required()
                            ->maxLength(255),
                        Select::make('color')
                            ->label('Цвет бейджа')
                            ->required()
                            ->default('amber')
                            ->options([
                                'amber' => 'Янтарный',
                                'red' => 'Красный',
                                'green' => 'Зеленый',
                                'slate' => 'Серо-сланцевый',
                                'sky' => 'Небесно-голубой',
                                'violet' => 'Фиолетовый',
                            ]),
                    ]),
            ]);
    }
}
