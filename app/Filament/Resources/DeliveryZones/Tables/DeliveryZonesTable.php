<?php

namespace App\Filament\Resources\DeliveryZones\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class DeliveryZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->columns([
                TextColumn::make('city')
                    ->label('Город'),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime(),
            ]);
    }
}
