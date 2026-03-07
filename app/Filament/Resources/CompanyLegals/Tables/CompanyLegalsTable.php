<?php

namespace App\Filament\Resources\CompanyLegals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyLegalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('legal_form')
                    ->searchable(),
                TextColumn::make('legal_email')
                    ->searchable(),
                TextColumn::make('owner')
                    ->searchable(),
                TextColumn::make('inn')
                    ->searchable(),
                TextColumn::make('ogrn')
                    ->searchable(),
                TextColumn::make('okpo')
                    ->searchable(),
                TextColumn::make('kpp')
                    ->searchable(),
                TextColumn::make('registration_address')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
