<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Domain\Client\Entity\Client;
use App\Filament\Resources\Clients\ClientResource;
use App\Support\Client\ClientStatusLabels;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ClientsTable
{
    /**
     * @param  class-string  $resourceClass
     */
    public static function configure(Table $table, string $resourceClass = ClientResource::class): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->withCount(['addresses', 'orders']),
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => ClientStatusLabels::statusLabel($state))
                    ->color(fn (string $state): string => ClientStatusLabels::statusColor($state)),
                IconColumn::make('consent_personal_data')
                    ->label('ПДн')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('consent_marketing')
                    ->label('Маркетинг')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('orders_count')
                    ->label('Заказы')
                    ->sortable(),
                TextColumn::make('addresses_count')
                    ->label('Адреса')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('birth_date')
                    ->label('Д.р.')
                    ->date('d.m.Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Удалён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ClientStatusLabels::statusOptions()),
                TernaryFilter::make('consent_personal_data')
                    ->label('Согласие на ПДн')
                    ->trueLabel('Да')
                    ->falseLabel('Нет'),
                TernaryFilter::make('consent_marketing')
                    ->label('Согласие на маркетинг')
                    ->trueLabel('Да')
                    ->falseLabel('Нет'),
                Filter::make('without_email')
                    ->label('Без email')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('email')),
                Filter::make('has_orders')
                    ->label('Есть заказы')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereHas('orders')),
                SelectFilter::make('trashed')
                    ->label('Удалённые')
                    ->options([
                        'with' => 'Включая удалённых',
                        'only' => 'Только удалённые',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'with' => $query->withTrashed(),
                            'only' => $query->onlyTrashed(),
                            default => $query,
                        };
                    }),
                Filter::make('created_at')
                    ->label('Дата регистрации')
                    ->schema([
                        DatePicker::make('created_from')->label('С'),
                        DatePicker::make('created_until')->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn ($record): string => $resourceClass::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->iconButton()
                    ->url(fn ($record): string => $resourceClass::getUrl('edit', ['record' => $record])),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('block')
                        ->label('Заблокировать')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($record) => $record->update(['status' => Client::STATUS_BLOCKED]),
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($record) => $record->update(['status' => Client::STATUS_ACTIVE]),
                        ))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
