<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Адреса доставки';

    protected static ?string $modelLabel = 'адрес';

    protected static ?string $pluralModelLabel = 'адреса';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('title')
            ->columns([
                TextColumn::make('type')
                    ->label('Тип')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('title')
                    ->label('Название')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('street')
                    ->label('Улица')
                    ->searchable(),
                TextColumn::make('house')
                    ->label('Дом')
                    ->searchable(),
                TextColumn::make('entrance')
                    ->label('Подъезд')
                    ->searchable(),
                TextColumn::make('apartment')
                    ->label('Квартира')
                    ->searchable(),
                IconColumn::make('deleted_at')
                    ->label('Удалён')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => (bool) $record->deleted_at),
                IconColumn::make('is_default')
                    ->label('По умолчанию')
                    ->boolean()
                    ->getStateUsing(function ($record): bool {
                        $client = $record->client;

                        return $client?->default_address_id === $record->id;
                    }),
            ])
            ->filters([
                Filter::make('active_only')
                    ->label('Только активные')
                    ->toggle()
                    ->default(true)
                    ->query(fn (Builder $query): Builder => $query->whereNull('deleted_at')),
                Filter::make('only_deleted')
                    ->label('Только удалённые')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('deleted_at')),
            ])
            ->headerActions([
                // Редактирование адресов — через сайт и API.
            ])
            ->recordActions([
                //
            ]);
    }
}
