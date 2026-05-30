<?php

namespace App\Filament\Marketing\Schemas;

use App\Filament\Marketing\Support\MarketingImageUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Контент')
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),
                    ]),
                Section::make('Изображение')
                    ->schema([
                        MarketingImageUpload::promotion(),
                    ]),
            ]);
    }
}
