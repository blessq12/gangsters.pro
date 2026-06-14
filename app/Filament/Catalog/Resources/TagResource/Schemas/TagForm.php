<?php

namespace App\Filament\Catalog\Resources\TagResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Тег')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        TextInput::make('label')
                            ->label('Подпись')
                            ->required()
                            ->maxLength(255),
                        Select::make('color')
                            ->label('Цвет')
                            ->options([
                                'amber' => 'Янтарный',
                                'blue' => 'Синий',
                                'cyan' => 'Голубой',
                                'emerald' => 'Изумрудный',
                                'fuchsia' => 'Фуксия',
                                'gray' => 'Серый',
                                'green' => 'Зелёный',
                                'indigo' => 'Индиго',
                                'lime' => 'Лайм',
                                'orange' => 'Оранжевый',
                                'pink' => 'Розовый',
                                'purple' => 'Фиолетовый',
                                'red' => 'Красный',
                                'rose' => 'Роза',
                                'sky' => 'Небесный',
                                'slate' => 'Сланцевый',
                                'stone' => 'Каменный',
                                'teal' => 'Бирюзовый',
                                'violet' => 'Фиалковый',
                                'yellow' => 'Жёлтый',
                                'zinc' => 'Цинковый',
                            ])
                            ->default('amber')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ]),
            ]);
    }
}
