<?php

namespace App\Filament\Operations\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Query\GetAdminClientListQuery;
use App\Filament\Operations\Concerns\ConfiguresHubTablePagination;
use App\Filament\Operations\Resources\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubClientsTable extends TableWidget
{
    use ConfiguresHubTablePagination;

    protected static ?string $heading = 'Клиенты';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $table = $table
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

                    return $this->buildEmptyHubLengthAwarePaginator($perPage);
                }

                return $this->buildHubLengthAwarePaginator(
                    $result,
                    max(1, (int) $page),
                    $perPage,
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
            ->headerActions([
                CreateAction::make()
                    ->label('Создать клиента')
                    ->url(ClientResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => ClientResource::getUrl('edit', ['record' => $record['id']])),
            ]);

        return $this->configureHubPagination($table, 'clients');
    }
}
