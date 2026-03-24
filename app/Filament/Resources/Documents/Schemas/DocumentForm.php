<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Документ')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('key')
                            ->label('Ключ')
                            ->helperText('Уникальный ключ в snake_case, например: return_policy')
                            ->regex('/^[a-z0-9]+(?:_[a-z0-9]+)*$/')
                            ->unique(ignoreRecord: true)
                            ->maxLength(120)
                            ->required()
                            ->validationMessages([
                                'regex' => 'Ключ должен быть в snake_case: только латиница, цифры и _.',
                            ]),
                        TextInput::make('title')
                            ->label('Название')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),
                Section::make('Содержимое')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('content')
                            ->label('Текст')
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }
}

