<?php

namespace App\Filament\MarketingContent\Resources\PromotionResource\Schemas;

use App\Filament\MarketingContent\Support\MarketingMediaUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Акция')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255),
                        MarketingMediaUpload::promotionImage(),
                        Textarea::make('body')
                            ->label('Тело акции (HTML)')
                            ->rows(10)
                            ->columnSpanFull()
                            ->helperText('Допускается HTML-разметка для модального окна акции.'),
                        Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                    ]),
            ]);
    }
}
