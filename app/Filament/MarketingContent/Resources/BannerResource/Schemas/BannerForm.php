<?php

namespace App\Filament\MarketingContent\Resources\BannerResource\Schemas;

use App\Filament\MarketingContent\Support\MarketingMediaUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Баннер')
                    ->columnSpanFull()
                    ->schema([
                        MarketingMediaUpload::bannerDesktop()->required(),
                        MarketingMediaUpload::bannerMobile()->required(),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ]),
            ]);
    }
}
