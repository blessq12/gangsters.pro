<?php

namespace App\Filament\Resources\CompanyLegals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class CompanyLegalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_id')
                    ->label('Компания (ID)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('legal_form')
                    ->label('Орг. форма')
                    ->searchable(),
                TextColumn::make('legal_email')
                    ->label('Юр. Email')
                    ->searchable(),
                TextColumn::make('owner')
                    ->label('Владелец')
                    ->searchable(),
                TextColumn::make('inn')
                    ->label('ИНН')
                    ->searchable(),
                TextColumn::make('ogrn')
                    ->label('ОГРН')
                    ->searchable(),
                TextColumn::make('okpo')
                    ->label('ОКПО')
                    ->searchable(),
                TextColumn::make('kpp')
                    ->label('КПП')
                    ->searchable(),
                TextColumn::make('registration_address')
                    ->label('Юридический адрес')
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
                EditAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
