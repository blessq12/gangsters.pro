<?php

namespace App\Filament\Company\Tables;

use App\Application\Company\Content\Document\Query\GetAdminDocumentListQuery;
use App\Filament\Company\Resources\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubDocumentsTable extends TableWidget
{
    protected static ?string $heading = 'Документы';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $items = app(GetAdminDocumentListQuery::class)->execute();

                return new LengthAwarePaginator(
                    collect($items)->keyBy('id'),
                    count($items),
                    max(count($items), 1),
                    1,
                    ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
                );
            })
            ->columns([
                TextColumn::make('key')->label('Ключ'),
                TextColumn::make('title')->label('Заголовок'),
                IconColumn::make('is_active')->label('Активен')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(DocumentResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => DocumentResource::getUrl('edit', ['record' => $record['id']])),
            ]);
    }
}
