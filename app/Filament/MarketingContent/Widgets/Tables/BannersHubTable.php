<?php

namespace App\Filament\MarketingContent\Widgets\Tables;

use App\Filament\MarketingContent\Support\MarketingHubTableActions;
use App\Filament\MarketingContent\Support\MarketingHubTablePresentation;
use App\Infrastructure\MarketingContent\Model\MKT_Banner;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class BannersHubTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return MarketingHubTableActions::forBanners(
            $table
                ->query(MKT_Banner::query())
                ->heading('Баннеры')
                ->description('Перетаскивайте строки, чтобы изменить порядок карусели на главной.')
                ->defaultSort('sort_order')
                ->reorderable('sort_order')
                ->columns([
                    TextColumn::make('title')
                        ->label('Заголовок')
                        ->searchable(),
                    ImageColumn::make('image_desktop')
                        ->label('Десктоп')
                        ->disk('public')
                        ->square(),
                    ImageColumn::make('image_mobile')
                        ->label('Мобила')
                        ->disk('public')
                        ->square(),
                    MarketingHubTablePresentation::activeStatusColumn(),
                ]),
        );
    }
}
