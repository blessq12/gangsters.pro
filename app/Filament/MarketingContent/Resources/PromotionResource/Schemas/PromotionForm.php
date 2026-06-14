<?php

namespace App\Filament\MarketingContent\Resources\PromotionResource\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        $maxUploadKb = self::maxUploadKilobytes('promotion');

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
                        FileUpload::make('image')
                            ->label('Изображение')
                            ->image()
                            ->disk('public')
                            ->directory('marketing/promotions')
                            ->visibility('public')
                            ->maxSize($maxUploadKb),
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

    private static function maxUploadKilobytes(string $key): ?int
    {
        $kb = (int) config("marketing.{$key}.max_upload_kb", 0);

        return $kb > 0 ? $kb : null;
    }
}
