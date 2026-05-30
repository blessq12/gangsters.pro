<?php

namespace App\Filament\Marketing\Tables;

use App\Application\Marketing\Promotion\Query\GetAdminPromotionListQuery;
use App\Filament\Marketing\Resources\PromotionResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubPromotionsTable extends TableWidget
{
    protected static ?string $heading = 'Акции';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $items = app(GetAdminPromotionListQuery::class)->execute();

                return new LengthAwarePaginator(
                    collect($items)->keyBy('id'),
                    count($items),
                    max(count($items), 1),
                    1,
                    ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
                );
            })
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('title')->label('Заголовок'),
                TextColumn::make('image')->label('Изображение'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(PromotionResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => PromotionResource::getUrl('edit', ['record' => $record['id']])),
            ]);
    }
}
