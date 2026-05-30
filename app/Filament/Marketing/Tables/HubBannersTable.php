<?php

namespace App\Filament\Marketing\Tables;

use App\Application\Marketing\Banner\Query\GetAdminBannerListQuery;
use App\Filament\Marketing\Resources\BannerResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubBannersTable extends TableWidget
{
    protected static ?string $heading = 'Баннеры';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $items = app(GetAdminBannerListQuery::class)->execute();

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
                    ->url(BannerResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => BannerResource::getUrl('edit', ['record' => $record['id']])),
            ]);
    }
}
