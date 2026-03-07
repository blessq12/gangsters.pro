<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eatsId')
                    ->searchable(),
                TextColumn::make('restaurantId')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('tel')
                    ->searchable(),
                TextColumn::make('street')
                    ->searchable(),
                TextColumn::make('house')
                    ->searchable(),
                TextColumn::make('building')
                    ->searchable(),
                TextColumn::make('staircase')
                    ->searchable(),
                TextColumn::make('floor')
                    ->searchable(),
                TextColumn::make('apartment')
                    ->searchable(),
                TextColumn::make('full_address')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deliveryDate')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deliveryType')
                    ->searchable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('itemsCost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deliveryFee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('change')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('delivery')
                    ->boolean(),
                TextColumn::make('comment')
                    ->searchable(),
                TextColumn::make('personQty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payType')
                    ->searchable(),
                TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('frontpad_id')
                    ->searchable(),
                TextColumn::make('discriminator')
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
