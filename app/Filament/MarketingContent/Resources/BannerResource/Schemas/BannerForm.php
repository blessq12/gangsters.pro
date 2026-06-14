<?php

namespace App\Filament\MarketingContent\Resources\BannerResource\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        $maxUploadKb = self::maxUploadKilobytes('banner');

        return $schema
            ->columns(2)
            ->components([
                Section::make('Баннер')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('image_desktop')
                            ->label('Изображение (десктоп)')
                            ->image()
                            ->disk('public')
                            ->directory('marketing/banners/desktop')
                            ->visibility('public')
                            ->maxSize($maxUploadKb),
                        FileUpload::make('image_mobile')
                            ->label('Изображение (мобила)')
                            ->image()
                            ->disk('public')
                            ->directory('marketing/banners/mobile')
                            ->visibility('public')
                            ->maxSize($maxUploadKb),
                        Toggle::make('is_active')
                            ->label('Активен')
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
