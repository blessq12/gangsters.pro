<?php

namespace App\Filament\Content\MarketingContent\Widgets\Tables;

use App\Filament\Content\MarketingContent\Support\MarketingHubTableActions;
use App\Filament\Content\MarketingContent\Support\MarketingHubTablePresentation;
use App\Infrastructure\Content\Model\MKT_Banner;
use App\Infrastructure\Content\Support\MarketingStoredPath;
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
                    TextColumn::make('id')
                        ->label('#')
                        ->sortable(),
                    ImageColumn::make('image_desktop')
                        ->label('Десктоп')
                        ->getStateUsing(
                            fn (MKT_Banner $record): ?string => MarketingStoredPath::filamentImageState($record->image_desktop),
                        )
                        ->disk('public')
                        ->visibility('public')
                        ->checkFileExistence(false)
                        ->imageHeight(72)
                        ->extraImgAttributes([
                            'class' => 'rounded-md object-cover bg-zinc-100 dark:bg-zinc-800',
                        ]),
                    ImageColumn::make('image_mobile')
                        ->label('Мобила')
                        ->getStateUsing(
                            fn (MKT_Banner $record): ?string => MarketingStoredPath::filamentImageState($record->image_mobile),
                        )
                        ->disk('public')
                        ->visibility('public')
                        ->checkFileExistence(false)
                        ->imageHeight(72)
                        ->extraImgAttributes([
                            'class' => 'rounded-md object-cover bg-zinc-100 dark:bg-zinc-800',
                        ]),
                    MarketingHubTablePresentation::activeStatusColumn(),
                ]),
        );
    }
}
