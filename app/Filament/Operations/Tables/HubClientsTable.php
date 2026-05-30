<?php

namespace App\Filament\Operations\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Query\GetAdminClientListQuery;
use App\Filament\Operations\Resources\ClientResource;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubClientsTable extends TableWidget
{
    protected static ?string $heading = 'Клиенты';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (
                ?string $search,
                ?array $filters,
                int|string $page,
                int|string $recordsPerPage,
            ): LengthAwarePaginator {
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;

                try {
                    $result = app(GetAdminClientListQuery::class)->execute(
                        search: filled($search) ? $search : null,
                        page: max(1, (int) $page),
                        perPage: $perPage,
                    );
                } catch (ApiException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();

                    return new LengthAwarePaginator([], 0, $perPage, 1);
                }

                return new LengthAwarePaginator(
                    collect($result['items'])->keyBy('id'),
                    $result['total'],
                    $perPage,
                    max(1, (int) $page),
                    ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
                );
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('name')
                    ->label('Имя'),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email'),
                TextColumn::make('status')
                    ->label('Статус'),
                TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->searchable()
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => ClientResource::getUrl('edit', ['record' => $record['id']])),
            ]);
    }
}
