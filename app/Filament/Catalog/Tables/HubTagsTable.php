<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Query\ListAdminTagsQuery;
use App\Filament\Catalog\Resources\TagResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class HubTagsTable extends TableWidget
{
    protected static ?string $heading = 'Теги';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): Collection {
                $items = app(ListAdminTagsQuery::class)->execute();

                return collect($items)->keyBy('id');
            })
            ->columns([
                TextColumn::make('label')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Код'),
                TextColumn::make('color')
                    ->label('Цвет'),
                TextColumn::make('sort_order')
                    ->label('Порядок'),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->searchable()
            ->paginated(false)
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => TagResource::getUrl('edit', ['record' => $record['id']])),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Создать тег')
                    ->url(TagResource::getUrl('create')),
            ]);
    }
}
