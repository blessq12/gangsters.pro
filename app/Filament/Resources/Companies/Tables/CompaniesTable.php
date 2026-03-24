<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Компания')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Описание')
                    ->searchable(),
                TextColumn::make('country')
                    ->label('Страна')
                    ->searchable(),
                TextColumn::make('state')
                    ->label('Регион')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Город')
                    ->searchable(),
                TextColumn::make('street')
                    ->label('Улица')
                    ->searchable(),
                TextColumn::make('house')
                    ->label('Дом')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('phone_additional')
                    ->label('Доп. телефон')
                    ->searchable(),
                TextColumn::make('email_address')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('logo')
                    ->searchable(),
                TextColumn::make('vk')
                    ->searchable(),
                TextColumn::make('inst')
                    ->searchable(),
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
