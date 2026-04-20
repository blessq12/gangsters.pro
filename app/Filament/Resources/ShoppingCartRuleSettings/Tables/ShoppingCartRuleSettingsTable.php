<?php

namespace App\Filament\Resources\ShoppingCartRuleSettings\Tables;

use App\Support\Money;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShoppingCartRuleSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                IconColumn::make('complement_rule_enabled')
                    ->label('Комплект')
                    ->boolean(),
                IconColumn::make('gift_rule_enabled')
                    ->label('Подарок')
                    ->boolean(),
                TextColumn::make('gift_threshold_kopecks')
                    ->label('Порог, ₽')
                    ->formatStateUsing(fn (int $state): float => Money::kopecksToApiRubles($state)),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->toggleable(),
            ]);
    }
}
