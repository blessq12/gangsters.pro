<?php

namespace App\Filament\MarketingContent\Widgets\Tables;

use App\Filament\MarketingContent\Support\MarketingHubTableActions;
use App\Filament\MarketingContent\Support\MarketingHubTablePresentation;
use App\Infrastructure\MarketingContent\Model\MKT_Promotion;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PromotionsHubTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return MarketingHubTableActions::forPromotions(
            $table
                ->query(MKT_Promotion::query())
                ->heading('Акции')
                ->description('Перетаскивайте строки, чтобы изменить порядок блока акций.')
                ->defaultSort('sort_order')
                ->reorderable('sort_order')
                ->columns([
                    TextColumn::make('title')
                        ->label('Заголовок')
                        ->searchable(),
                    MarketingHubTablePresentation::activeStatusColumn(),
                ]),
        );
    }
}
